<?php

namespace App\Admin\Controllers\System;


use App\Admin\Controller;
use App\Admin\Models\Plugin;
use App\Admin\Requests\PluginRequest;
use App\Models\Agreement;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Xin\Hint\Facades\Hint;
use Xin\Menu\Facades\Menu;
use Xin\Support\File;

class PluginController extends Controller
{
    use InteractsEvent;

    /**
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * @param \think\App $app
     * @param PluginManager $pluginManager
     */
    public function __construct(Application $app, PluginManager $pluginManager)
    {
        parent::__construct($app);
        $this->pluginManager = $pluginManager;
    }

    /**
     * 列表查询
     * @return View
     */
    public function index(Request $request)
    {
        $install = $request->input('install');
        $install = $install === '' ? null : (int)$install;

        $search = $request->query();
        $data = Plugin::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();


        return Hint::result($data);
    }

    /**
     * 数据创建
     * @param PluginRequest $request
     * @return Response
     */
    public function store(PluginRequest $request)
    {
        $data = $request->validated();

        /** @var Plugin $info */
        $info = DB::transaction(function () use (&$data) {
            $data['config'] = new \stdClass();
            $data['events'] = isset($data['events']) ? $data['events'] : [];
            $info = Plugin::create($data);
            $this->initPlugin($info, $data);

            return $info;
        });

        $this->updateCache();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return View
     */
    public function show(Request $request)
    {
        $id = $request->validId();

        $info = Plugin::query()->where('id', $id)->firstOrFail();
        $this->assignEvents();

        return view('plugin.show', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新表单
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $id = $request->validId();

        $info = Plugin::query()->where('id', $id)->firstOrFail();
        $this->assignEvents();

        return view('plugin.edit', [
            'info' => $info,
        ]);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(PluginRequest $request)
    {
        $id = $request->validId();

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        $this->updateCache();

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = (int)$request->input('force', 0);

        Plugin::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 安装插件
     *
     * @return Response
     */
    public function install()
    {
        /** @var DatabasePlugin $info */
        $info = $this->findIsEmptyAssert();
        if ($info->install) {
            return Hint::success("应用已安装！");
        }

        if (!$info->local_version) {
            return Hint::error("应用已删除！");
        }

        try {
            $pluginInfo = $this->pluginManager->installPlugin($info->name);

            $this->updateInfo($pluginInfo);

            // 更新配置
            $info->save([
                'install' => 1,
                'version' => $pluginInfo->getVersion(),
            ]);
        } catch (\Exception $e) {
            return Hint::error($e->getMessage());
        }

        return Hint::success("应用已安装！");
    }

    /**
     * 卸载插件
     *
     * @return Response
     */
    public function uninstall()
    {
        /** @var DatabasePlugin $info */
        $info = $this->findIsEmptyAssert();
        if (!$info->install) {
            return Hint::success("应用已卸载！");
        }

        try {
            $pluginInfo = $this->pluginManager->uninstallPlugin($info->name);

            $pluginDetail = $pluginInfo->getInfo();

            // 卸载事件
            if (isset($pluginDetail['events'])) {
                DatabaseEvent::unmountAddon($info->name);
            }

            // 配置菜单
            if (isset($pluginDetail['menus'])) {
                $this->updateMenus(false, $pluginDetail['menus'], $pluginInfo);
            }

            $info->save(['install' => 0]);
        } catch (PluginNotFoundException $e) {
            DatabaseEvent::unmountAddon($info->name);
            $info->save(['install' => 0]);

            return Hint::success("应用目录已被删除！");
        } catch (\Exception $e) {
            return Hint::error($e->getMessage());
        }

        return Hint::success("应用已卸载！");
    }

    /**
     * 升级插件
     *
     * @return Response
     * @throws \Xin\Plugin\Contracts\PluginNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function upgrade()
    {
        /** @var DatabasePlugin $info */
        $info = $this->findIsEmptyAssert();
        if (!$info->install) {
            return Hint::success("应用已卸载！");
        }

        if (!$info->local_version) {
            return Hint::error("应用已删除！");
        }

        try {
            $pluginInfo = $this->pluginManager->plugin($info->name);
            $this->updateInfo($pluginInfo);
            $info->save([
                'version' => $pluginInfo->getVersion(),
            ]);
        } catch (\Exception $e) {
            return Hint::error($e->getMessage());
        }

        return Hint::success("已更新信息");
    }

    /**
     * 更新插件信息
     *
     * @param \Xin\Plugin\Contracts\PluginInfo $pluginInfo
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function updateInfo(PluginInfoContract $pluginInfo)
    {
        // 获取插件描述信息
        $pluginDetail = $pluginInfo->getInfo();

        // 生成静态资源软链接
        $this->createStaticSymlink($pluginInfo);

        // 安装事件
        if (isset($pluginDetail['events'])) {
            DatabaseEvent::mountAddon($pluginInfo->getName(), $pluginDetail['events']);
        }

        // 配置菜单
        $this->updateMenus(true, $pluginInfo);
    }

    /**
     * 设置菜单
     *
     * @param bool $isInstall
     * @param \Xin\Plugin\Contracts\PluginInfo $pluginInfo
     */
    protected function updateMenus($isInstall, PluginInfoContract $pluginInfo)
    {
        $menuGuards = array_keys($this->app->config->get('menu.menus'));
        foreach ($menuGuards as $guard) {
            if ($isInstall) {
                $menusFilename = $pluginInfo->path($guard) . "menus.php";
                if (!file_exists($menusFilename)) {
                    continue;
                }
                $menusData = require_once $menusFilename;
                Menu::menu($guard)->puts($menusData, $pluginInfo->getName());
            } else {
                Menu::menu($guard)->forget([
                    'plugin' => $pluginInfo->getName(),
                ]);
            }
        }
    }

    /**
     * 插件配置
     *
     * @return Response
     */
    public function config(Request $request)
    {
        /** @var DatabasePlugin $info */
        $info = $this->findIsEmptyAssert();
        if (!$info->install) {
            return Hint::success("插件未安装！");
        }

        // 获取插件实例
        $pluginInfo = $this->pluginManager->plugin($info->name);

        if (!$request->isPost()) {
            // 获取插件配置
            $this->assign([
                'info' => $info,
                'config_tpl' => $pluginInfo->getConfigTemplate((array)$info->config),
            ]);

            return $this->fetch();
        }

        $config = $request->input('config/a', []);
        foreach ($pluginInfo->getConfigTypeList() as $key => $type) {
            if (!isset($config[$key])) {
                continue;
            }

            if ('int' == $type) {
                $config[$key] = (int)$config[$key];
            } elseif ('float' == $type) {
                $config[$key] = (float)$config[$key];
            } elseif ('array' == $type) {
                $config[$key] = Arr::parse($config[$key]);
            }
        }

//        $info->config = array_merge((array)$info->config, $config);
        $info->config = $config;

        $info->save();

        return Hint::success('配置已更新！');
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function findIsEmptyAssert($id = null)
    {
        if ($id) {
            return Plugin::findOrFail($id);
        }

        if ($request->has('name')) {
            return Plugin::query()->where('name', $request->validString('name'))->findOrFail($id);
        }

        return Plugin::findOrFail($request->validId());
    }

    /**
     * 重新刷新插件菜单
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \Xin\Plugin\Contracts\PluginNotFoundException
     */
    public function refreshMenus(Request $request)
    {
        $plugin = $request->input('plugin', '', 'trim');

        $menuGuards = array_keys($this->app->config->get('menu.menus'));
        foreach ($menuGuards as $guard) {
            Menu::menu($guard)->refresh($plugin);
        }

        DatabasePlugin::query()->where([
            'install' => 1,
            'status' => 1,
        ])->when($plugin, ['name' => $plugin])
            ->select()
            ->each(function (DatabasePlugin $info) {
                if (!$this->pluginManager->has($info->name)) {
                    return;
                }

                $pluginInfo = $this->pluginManager->plugin($info->name);

                // 配置菜单
                $this->updateMenus(true, $pluginInfo);
            });

        return Hint::success("已刷新！");
    }

    /**
     * 创建插件资源目录软链接
     *
     * @param \Xin\Plugin\Contracts\PluginInfo $pluginInfo
     */
    protected function createStaticSymlink(PluginInfoContract $pluginInfo)
    {
        $pluginName = $pluginInfo->getName();
        $pluginStaticPath = $pluginInfo->path('static');
        $linkPath = public_path('vendor') . $pluginName;

        // 检查原路径是否存在
        if (!is_dir($pluginStaticPath)) {
            return;
        }

        // 检查目标路径是否存在，如果存在则需要删除
        if (is_dir($linkPath)) {
            @rmdir($linkPath);
        }
        if (file_exists($linkPath) || @lstat($linkPath)) {
            @unlink($linkPath);
        }

        // 创建软链接
        if (!@symlink($pluginStaticPath, $linkPath)) {
            throw new \LogicException('资源目录软链接创建失败，请检查 public 目录是否有可写权限！');
        }
    }

    /**
     * 更新缓存
     * @return void
     */
    protected function updateCache()
    {
        DatabaseEvent::refreshCache();
        DatabasePlugin::refreshPluginDisabledListCache();
    }

    /**
     * 初始化插件
     *
     * @param Plugin $pluginModel
     * @param array $data
     */
    protected function initPlugin(Plugin $pluginModel, array $data)
    {
        $pluginName = $pluginModel->name;

        /** @var PluginManager $pluginManager */
        $pluginManager = app(PluginManager::class);
        $pluginRootPath = $pluginManager->pluginPath($pluginName);

        // 插件已存在，则不在处理
        if (is_dir($pluginRootPath)) {
            Hint::outputSuccess("插件目录已存在，文件将不在生成！");
        }

        $createDirs = [$pluginRootPath];

        // 是否生成事件目录
        $eventDirs = [
            0 => 'weight',
            1 => 'listener',
        ];
        $events = [];
        if (!empty($data['events'])) {
            $events = DatabaseEvent::query()->where('name', 'in', $data['events'])->pluck('type', 'name');
            $eventTypes = array_unique(array_values($events));
            foreach ($eventDirs as $key => $dir) {
                if (in_array($key, $eventTypes, true)) {
                    $createDirs[] = $pluginRootPath . $dir . DIRECTORY_SEPARATOR;
                }
            }
        }

        // 创建插件目录
        File::createDirOrFiles($createDirs);

        // 创建插件信息文件
        $manifestPath = $pluginRootPath . "manifest.php";
        file_put_contents($manifestPath, $this->buildManifestContent($data));

        $pluginPath = $pluginRootPath . "Plugin.php";
        file_put_contents($pluginPath, $this->buildPluginContent($data));

        // 创建配置文件
        if (isset($data['config_tpl']) && !empty($data['config_tpl'])) {
            $pluginConfigTplPath = $pluginRootPath . "config.php";
            file_put_contents($pluginConfigTplPath, $data['config_tpl']);
        }

        // 创建事件文件
        if (!empty($events)) {
            foreach ($events as $event => $type) {
                $subDir = $eventDirs[$type];
                $eventClass = Str::studly($event);
                $eventFilePath = $pluginRootPath . $subDir . DIRECTORY_SEPARATOR . $eventClass . ".php";
                file_put_contents($eventFilePath, $this->buildEventContent($type, $eventClass, $subDir, $pluginName));
            }
        }

        // 创建后台文件
        if (isset($data['has_admin']) && $data['has_admin']) {
            $className = Str::studly($data['name']);

            $adminRootPath = $pluginRootPath . 'admin' . DIRECTORY_SEPARATOR;
            File::createDirOrFiles([
                $pluginRootPath . 'model' . DIRECTORY_SEPARATOR,
                $pluginRootPath . 'validate' . DIRECTORY_SEPARATOR,
                $adminRootPath . 'controller' . DIRECTORY_SEPARATOR,
                $adminRootPath . 'view' . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR,
            ]);
            file_put_contents(
                $pluginRootPath . 'model' . DIRECTORY_SEPARATOR . "{$className}.php",
                $this->buildModelContent($className, $data)
            );
            file_put_contents(
                $pluginRootPath . 'validate' . DIRECTORY_SEPARATOR . "{$className}Validate.php",
                $this->buildValidateContent($className, $data)
            );
            file_put_contents(
                $adminRootPath . 'controller' . DIRECTORY_SEPARATOR . "IndexController.php",
                $this->buildAdminControllerContent($className, $data)
            );
            file_put_contents(
                $adminRootPath . 'view' . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . "index.html",
                $this->buildAdminViewContent($data)
            );
            file_put_contents(
                $adminRootPath . "menus.php",
                $this->buildAdminMenusConfigContent($data)
            );
        }
    }

    /**
     * 生成插件描述文件内容
     *
     * @param array $data
     * @return string
     */
    protected function buildManifestContent($data)
    {
        $info = var_export([
            'name' => $data['name'],
            'title' => $data['title'],
            'description' => $data['description'],
            'author' => $data['author'],
            'version' => $data['version'],
            'events' => $data['events'],
        ], true);

        return <<<EOT
<?php
return $info;
EOT;
    }

    /**
     * 生成插件文件内容
     *
     * @param array $data
     * @return string
     */
    protected function buildPluginContent($data)
    {
        return <<<EOT
<?php
namespace plugins\\{$data['name']};

use Xin\Plugin\Contracts\Factory as PluginManager;
use Xin\Plugin\Contracts\Plugin as PluginContract;
use Xin\Plugin\Contracts\PluginInfo;

class Plugin implements PluginContract{

	/**
	 * @inheritDoc
	 */
	public function install(PluginInfo \$pluginInfo, PluginManager \$pluginManager){
	}

	/**
	 * @inheritDoc
	 */
	public function uninstall(PluginInfo \$pluginInfo, PluginManager \$pluginManager){
	}

	/**
	 * @inheritDoc
	 */
	public function upgrade(PluginInfo \$pluginInfo, PluginManager \$pluginManager, \$version){
	}

	/**
	 * @inheritDoc
	 */
	public function boot(PluginInfo \$pluginInfo, PluginManager \$pluginManager){
	}
}
EOT;
    }

    /**
     * 生成事件文件内容
     *
     * @param string $eventClass
     * @param string $subDir
     * @param string $pluginName
     * @return string
     */
    protected function buildEventContent($type, $eventClass, $subDir, $pluginName)
    {
        if ($type == 0) {
            return <<<EOT
<?php
namespace plugins\\$pluginName\\$subDir;

use Xin\\ThinkPHP\\Foundation\\Weight;

class $eventClass extends Weight{

	/**
	 * @return string|void
	 */
	protected function render(){
	}
}
EOT;
        }

        return <<<EOT
<?php
namespace plugins\\$pluginName\\$subDir;

use Xin\\ThinkPHP\\Foundation\\Weight;

class $eventClass{

	/**
	 * @return void
	 */
	protected function handle(){
	}
}
EOT;
    }

    /**
     * 生产配置文件
     *
     * @return string
     */
    protected function buildConfigTplContent()
    {
        return <<<EOT
<?php
return [
	[
		'title'  => '基本',
		'config' => [
			[
				'title' => '小挂件是否显示',
				'name'  => 'display',
				'type'  => 'switch',
				'value' => 1,
			],
		],
	],
];
?>
EOT;
    }

    /**
     * 创建模型类
     *
     * @param array $data
     * @return string
     */
    protected function buildModelContent($className, $data)
    {
        return <<<EOT
<?php
namespace plugins\\{$data['name']}\\model;

use app\\common\\model\\Model;

class {$className} extends Model
{

}
EOT;
    }

    /**
     * 创建验证器类
     *
     * @param array $data
     * @return string
     */
    protected function buildValidateContent($className, $data)
    {
        return <<<EOT
<?php
namespace plugins\\{$data['name']}\\validate;

use think\\Validate;

class {$className}Validate extends Validate
{

}
EOT;
    }

    /**
     * 创建后台控制器
     *
     * @param array $data
     * @return string
     */
    protected function buildAdminControllerContent($className, $data)
    {
        return <<<EOT
<?php
namespace plugins\\{$data['name']}\\admin\\controller;

use app\admin\Controller;
use think\db\Query;
use think\Model;
use plugins\\{$data['name']}\\model\\{$className};
use plugins\\{$data['name']}\\validate\\{$className}Validate;

class IndexController extends Controller{

	/**
	 * 数据列表
	 * @return string
	 * @throws \\think\\db\\exception\\DbException
	 */
	public function index()
	{
		\$search = \$request->get();
		\$data = {$className}::simple()->search(\$search)
			->order('id desc')
			->paginate(\);

		\$this->assign('data', \$data);

		return \$this->fetch();
	}

	/**
	 * 创建数据
	 * @return string|\\think\\Response
	 * @throws \\think\\db\\exception\\DataNotFoundException
	 * @throws \\think\\db\\exception\\DbException
	 * @throws \\think\\db\\exception\\ModelNotFoundException
	 */
	public function create()
	{
		\$isCopy = \$request->input('copy', 0);
		\$id = \$request->input('id/d', 0);

		if (\$request->isGet()) {
			if (\$isCopy && \$id > 0) {
				\$info = {$className}::query()->where('id', \$id)->find();
				\$this->assign('info', \$info);
			}

			return \$this->fetch('edit');
		}


		\$data = \$request->validate(null, {$className}Validate::class);
		\$info = {$className}::create(\$data);

		return Hint::success("创建成功！", null, \$info);
	}

	/**
	 * 更新数据
	 * @return string|\\think\\Response
	 * @throws \\think\\db\\exception\\DataNotFoundException
	 * @throws \\think\\db\\exception\\DbException
	 * @throws \\think\\db\\exception\\ModelNotFoundException
	 */
	public function update()
	{
		\$id = \$request->validId();
		\$info = {$className}::query()->where('id', \$id)->firstOrFail();

		if (\$request->isGet()) {
			\$this->assign('info', \$info);

			return \$this->fetch('edit');
		}

		\$data = \$request->validate(null, {$className}Validate::class);
		if (!\$info->save(\$data)) {
			return Hint::error("更新失败！");
		}

		return Hint::success("更新成功！", null, \$info);
	}

	/**
	 * 删除数据
	 * @return \\think\\Response
	 * @throws \\think\\db\\exception\\DataNotFoundException
	 * @throws \\think\\db\\exception\\DbException
	 * @throws \\think\\db\\exception\\ModelNotFoundException
	 */
	public function delete()
	{
		\$ids = \$request->validIds();
		\$isForce = \$request->integer('force', 0);

		{$className}::query()->whereIn('id', \$ids)->select()->each(function (Model \$item) use (\$isForce) {
			\$item->force(\$isForce)->delete();
		});

		return Hint::success('删除成功！', null, \$ids);
	}
}
EOT;
    }

    /**
     * 生产菜单配置文件
     *
     * @param array $data
     * @return string
     */
    protected function buildAdminMenusConfigContent($data)
    {
        return <<<EOT
<?php
return [
	[
		'title'  => '{$data['title']}',
		'url'    => '{$data['name']}>index/index',
		'show'   => true,
		'link'   => true,
		'icon'   => 'fa fa-gavel',
		'sort'   => 300,
		'parent' => 'marketing',
		'child'  => [
			[
				'title' => '新增{$data['title']}',
				'url'   => '{$data['name']}>index/create',
			],
			[
				'title' => '更新{$data['title']}',
				'url'   => '{$data['name']}>index/update',
			],
		],
	],
];
EOT;
    }

    /**
     * 创建后台页面文件
     *
     * @param array $data
     * @return string
     */
    protected function buildAdminViewContent($data)
    {
        return <<<EOT
{extend name='layout'/}

{block name="assign"}
{assign name="page_title" value="{$data['title']}管理" /}
{/block}

{block name='body'}
<div class="card">

	<div class="card-header pb-0">
		<form class="form-row align-items-center form-filter">
			<input type="hidden" name="status" value="{\$status}"/>
			<div class="form-group col-auto">
				<label>关键字</label>
				<input type="text" name="keywords" placeholder="请输入" value="{\$Request.get.keywords}" class="form-control"/>
			</div>

			<div class="form-group col-auto" style="margin-top: 28px">
				<button class="btn btn-primary">
					<i class="fa fa-search"></i>
				</button>
				<a href="{:plugin_url()}" class="btn btn-secondary">
					<i class="fa fa-undo"></i>
				</a>
			</div>
		</form>
	</div>

	<div class="card-body">
		<div class="mb-3">
			<a href="{:plugin_url('create')}" class="btn btn-primary">添加</a>
			<a href="{:plugin_url('delete')}" class="btn btn-danger"
					data-ajax-get="{confirm:true,mustTargetQuery:true,target:'[name=\'ids[]\']'}">删除</a>
		</div>

		<div class="table-responsive">
			<table class="table table-hover table-centered">
				<thead>
				<tr>
					<th style="width:24px">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" name="ids[]" id="all-checkbox" class="custom-control-input"
									data-choice-check data-target="tbody [name='ids[]']"/>
							<label class="custom-control-label" for="all-checkbox"></label>
						</div>
					</th>
					<th>名称</th>
					<th class="text-center" style="width:48px">状态</th>
					<th class="text-center" style="width:140px">操作</th>
				</tr>
				</thead>
				<tbody>
				{volist name="data" id="vo"}
				<tr>
					<td>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" value="{\$vo.id}" name="ids[]" id="item_{\$vo.id}" class="custom-control-input"/>
							<label class="custom-control-label" for="item_{\$vo.id}"></label>
						</div>
					</td>
					<td class="text-nowrap" data-preview>
						<img src="{\$vo.cover}?imageView2/1/q/75" class="bg-light rounded" alt=""
								data-src="{\$vo.cover}" mode="aspectFill" style="width:32px" height="32"/>
						<span>{\$vo.title}</span>
					</td>
					<td class="text-center">
						{if \$vo.status==0}
						<span>禁用</span>
						{else/}
						<span class="text-success">启用</span>
						{/if}
					</td>
					<td class="actions">
						<a href="{:plugin_url('update',['id'=>\$vo.id])}">编辑</a>
						<div class="dropdown">
							<a class="dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">更多</a>
							<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
								<a class="dropdown-item" href="{:plugin_url('update',['id'=>\$vo.id,'copy'=>1])}" target="_blank">复制</a>
								<a class="dropdown-item text-danger" href="{:plugin_url('delete',['ids'=>\$vo.id])}" data-ajax-get="{confirm:true}">删除</a>
							</div>
						</div>
					</td>
				</tr>
				{/volist}
				</tbody>
			</table>
			{include file="empty"/}
			{\$data|raw}
		</div>
	</div>
</div>
{/block}

{block name='foot'}
<script>
</script>
{/block}
EOT;
    }

}
