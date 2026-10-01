<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_floatingbutton;

/**
 * Tests for the floating button block.
 *
 * @package    block_floatingbutton
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class block_floatingbutton_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Buttons pointing at activities hidden from the user are not rendered.
     *
     * @covers \block_floatingbutton::get_content
     */
    public function test_get_content_hides_invisible_activities(): void {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
        require_once($CFG->dirroot . '/blocks/floatingbutton/block_floatingbutton.php');
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $hidden = $generator->create_module('page', ['course' => $course->id, 'name' => 'Hidden page', 'visible' => 0]);
        $visible = $generator->create_module('page', ['course' => $course->id, 'name' => 'Visible page']);
        $student = $generator->create_and_enrol($course, 'student');
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $config = (object) [
            'icon_number' => 2,
            'icon' => ['fa-eye', 'fa-star'],
            'type' => ['internal', 'internal'],
            'cmid' => ['cmid=' . $hidden->cmid, 'cmid=' . $visible->cmid],
            'name' => ['', ''],
            'customlayout' => [0, 0],
            'defaultbackgroundcolor' => '#000000',
            'defaulttextcolor' => '#ffffff',
        ];
        $render = function (\stdClass $user) use ($course, $config): string {
            $this->setUser($user);
            $page = new \moodle_page();
            $page->set_course($course);
            $page->set_url('/course/view.php', ['id' => $course->id]);
            $block = new \block_floatingbutton();
            $block->page = $page;
            $block->config = $config;
            $block->get_content();
            return $block->content->text;
        };

        $studenttext = $render($student);
        $this->assertStringNotContainsString('Hidden page', $studenttext);
        $this->assertStringContainsString('Visible page', $studenttext);

        $this->assertStringContainsString('Hidden page', $render($teacher));
    }
}
