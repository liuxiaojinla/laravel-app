<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Admin\Controllers;

use app\admin\Controller;
use Plugins\Order\App\Http\Requests\ExpressValidate;
use Plugins\Order\App\Models\Express;

class ExpressController extends Controller
{

    use InteractsCURD;

    /**
     * @var string
     */
    protected $model = Express::class;

    /**
     * @var string
     */
    protected $validator = ExpressValidate::class;

    /**
     * @var string[]
     */
    protected $allowFields = [
        'sort' => 'number|min:0',
    ];

    /**
     * @var string
     */
    protected $keywordField = 'title';

    /**
     * @param Query $query
     */
    protected function querySelect(Query $query)
    {
        $query->removeOption('order')->order([
            'sort' => 'asc',
            'id'   => 'desc',
        ]);
    }

}
