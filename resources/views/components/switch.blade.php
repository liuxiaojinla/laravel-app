<!-- On: "bg-indigo-600", Off: "bg-gray-200" -->
<span x-data="{'open':false}" :aria-checked="open"
      :class="open?'bg-indigo-600':'bg-gray-200'"
      class="relative inline-block flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:shadow-outline"
      role="checkbox" tabindex="0"
      @click="open=!open">
  <!-- On: "translate-x-5", Off: "translate-x-0" -->
  <span :aria-hidden="!open"
        :class="open?'translate-x-5':'translate-x-0'"
        class="inline-block h-5 w-5 rounded-full bg-white shadow transform transition ease-in-out duration-200"></span>
</span>
