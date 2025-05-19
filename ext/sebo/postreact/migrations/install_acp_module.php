<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org - Thanks Chris1278!
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
 "`t" * ($matches[0].Length / 4) public static function depends_on()
 "`t" * ($matches[0].Length / 4) {
 "`t" * ($matches[0].Length / 4) return ['\sebo\postreact\migrations\install_sample_data'];
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) public function update_data()
 "`t" * ($matches[0].Length / 4) {
 "`t" * ($matches[0].Length / 4) return [

 "`t" * ($matches[0].Length / 4) ['module.add', [
 "`t" * ($matches[0].Length / 4) 'acp',
 "`t" * ($matches[0].Length / 4) 'ACP_CAT_DOT_MODS',
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_TITLE'
 "`t" * ($matches[0].Length / 4) ]],
 "`t" * ($matches[0].Length / 4) ['module.add', [
 "`t" * ($matches[0].Length / 4) 'acp',
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_TITLE',
 "`t" * ($matches[0].Length / 4) [
 "`t" * ($matches[0].Length / 4) 'module_basename'   => '\sebo\postreact\acp\main_module',
 "`t" * ($matches[0].Length / 4) 'modes'             => ['settings'],
 "`t" * ($matches[0].Length / 4) ],
 "`t" * ($matches[0].Length / 4) ]],
 "`t" * ($matches[0].Length / 4) ];
 "`t" * ($matches[0].Length / 4) }
}
