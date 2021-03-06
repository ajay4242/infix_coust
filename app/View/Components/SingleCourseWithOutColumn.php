<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SingleCourseWithOutColumn extends Component
{
    private $course, $hover;

    public function __construct($course, $hover = 'left')
    {
        $this->course = $course;
        $this->hover = $hover;
    }


    public function render()
    {
        $course = $this->course;
        $hover = $this->hover;
        return view(theme('components.single-course-with-out-column'), compact('course', 'hover'));
    }

}
