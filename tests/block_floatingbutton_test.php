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
 * Tests for block_floatingbutton.
 *
 * @package    block_floatingbutton
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class block_floatingbutton_test extends \advanced_testcase {
    /**
     * Renders a floating button block with a single external link.
     *
     * @param string $externalurl the configured external url
     * @return string the rendered block content
     */
    private function render_external_link(string $externalurl): string {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $config = (object) [
            'icon_number' => 1,
            'icon' => ['fa-link'],
            'name' => ['Link'],
            'type' => ['external'],
            'externalurl' => [$externalurl],
            'customlayout' => [0],
            'defaultbackgroundcolor' => '#000000',
            'defaulttextcolor' => '#ffffff',
        ];
        $block = $this->getDataGenerator()->create_block('floatingbutton', [
            'parentcontextid' => \context_course::instance($course->id)->id,
            'configdata' => base64_encode(serialize($config)),
        ]);
        $record = $DB->get_record('block_instances', ['id' => $block->id]);
        $record->visible = 1;
        $page = new \moodle_page();
        $page->set_course($course);
        $page->set_url(new \moodle_url('/course/view.php', ['id' => $course->id]));
        return block_instance('floatingbutton', $record, $page)->content->text;
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A javascript: url configured as external link must not reach the href attribute.
     *
     * @covers \block_floatingbutton::get_content
     */
    public function test_external_javascript_url_is_dropped(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = $this->render_external_link('javascript:0?alert(document.cookie):0');

        $this->assertStringContainsString('block_floatingbutton-0', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * An https url configured as external link is rendered escaped in the href attribute.
     *
     * @covers \block_floatingbutton::get_content
     */
    public function test_external_https_url_is_rendered(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = $this->render_external_link('https://example.org/page?a=1&b=2');

        $this->assertStringContainsString('href="https://example.org/page?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }
}
