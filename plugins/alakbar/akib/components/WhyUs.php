<?php namespace Alakbar\Akib\Components;

use Cms\Classes\ComponentBase;
use Alakbar\Akib\Models\Settings;

class WhyUs extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Why Us Component',
            'description' => 'Displays reasons why users should choose us.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['sections'] = Settings::get('sections');
    }
}
