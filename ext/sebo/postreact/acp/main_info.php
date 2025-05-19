<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\acp;

/**
 * PostReaction ACP module info.
 */
class main_info
{
 '`t' * ($matches[0].Length / 4) public function module()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4) 'filename'  => '\sebo\postreact\acp\main_module',
 '`t' * ($matches[0].Length / 4) 'title'     => 'ACP_POSTREACT_TITLE',
 '`t' * ($matches[0].Length / 4) 'modes'     => [
 '`t' * ($matches[0].Length / 4) 'settings'  => [
 '`t' * ($matches[0].Length / 4) 'title' => 'ACP_POSTREACT',
 '`t' * ($matches[0].Length / 4) 'auth'  => 'ext_sebo/postreact && acl_a_board',
 '`t' * ($matches[0].Length / 4) 'cat'   => ['ACP_POSTREACT_TITLE'],
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
}
