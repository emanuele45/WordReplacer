<?php
/**
 * Word Replacer
 * From time to time is better to separate word censoring and word replacement
 * Replaces text only in the bodies
 *
 * @package WRR
 * @author emanuele
 * @copyright 2014 emanuele
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * Part of the code is:
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 */

// Form for stopping people using naughty words, etc.
function template_edit_wordreplacer()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// First section is for adding/removing words from the censored list.
	echo '
	<div id="admincenter">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=addonsettings;sa=wordreplacer" method="post" accept-charset="UTF-8">
			<h3 class="catbg">
				', $txt['wordreplacer'], '
			</h3>
			<div class="windowbg2">
				<div class="content">
					<p>', $txt['wordreplacer_where'], '</p>';

	// Show text boxes for censoring [bad   ] => [good  ].
	foreach ($context['censored_words'] as $vulgar => $proper)
		echo '
					<div class="censorWords">
						<input type="text" name="censor_vulgar[]" value="', $vulgar, '" size="30" /> <i class="fa  fa-arrow-circle-right"></i> <input type="text" name="censor_proper[]" value="', $proper, '" size="30" />
					</div>';

	// Now provide a way to censor more words.
	echo '
					<div class="censorWords">
						<input type="text" name="censor_vulgar[]" size="30" class="input_text" /> <i class="fa  fa-arrow-circle-right"></i> <input type="text" name="censor_proper[]" size="30" class="input_text" />
					</div>
					<div id="moreCensoredWords"></div><div class="censorWords" style="display: none;" id="moreCensoredWords_link">
						<a class="linkbutton_left" href="#;" onclick="addNewWord(); return false;">', $txt['censor_clickadd'], '</a><br />
					</div>
					<script><!-- // --><![CDATA[
						document.getElementById("moreCensoredWords_link").style.display = "";
					// ]]></script>
					<hr class="clear" />
					<dl class="settings">
						<dt>
							<label for="censorWholeWord_check">', $txt['censor_whole_words'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="censorWholeWord" value="1" id="censorWholeWord_check"', empty($modSettings['censorWholeWord']) ? '' : ' checked="checked"', ' class="input_check" />
						</dd>
						<dt>
							<label for="censorIgnoreCase_check">', $txt['censor_case'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="censorIgnoreCase" value="1" id="censorIgnoreCase_check"', empty($modSettings['censorIgnoreCase']) ? '' : ' checked="checked"', ' class="input_check" />
						</dd>
						<dt>
							<a href="' . $scripturl . '?action=quickhelp;help=allow_no_censored" onclick="return reqOverlayDiv(this.href);" class="help"><img src="' . $settings['images_url'] . '/helptopics.png" class="icon" alt="' . $txt['help'] . '" /></a><label for="allow_no_censored">', $txt['censor_allow'], ':</label></a></dt>
						</dt>
						<dd>
							<input type="checkbox" name="censorAllow" value="1" id="allow_no_censored"', empty($modSettings['allow_no_censored']) ? '' : ' checked="checked"', ' class="input_check" />
						</dd>
					</dl>
					<input type="submit" name="save_censor" value="', $txt['save'], '" class="right_submit" />
				</div>
			</div>
			<br />';

	// This lets you test out your filters by typing in rude words and seeing what comes out.
	echo '
			<h3 class="category_header">', $txt['censor_test'], '</h3>
			<div class="content">
				<div class="centertext">
					<p id="censor_result" style="display:none" class="infobox">', empty($context['censor_test']) ? '' : $context['censor_test'], '</p>
					<input id="censortest" type="text" name="censortest" value="', empty($context['censor_test']) ? '' : $context['censor_test'], '" class="input_text" />
					<input id="preview_button" type="submit" value="', $txt['censor_test_save'], '" class="button_submit" />
				</div>
			</div>
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input id="token" type="hidden" name="', $context['admin-censor_token_var'], '" value="', $context['admin-censor_token'], '" />
		</form>
	</div>
	<script><!-- // --><![CDATA[
		$(document).ready(function() {
			$("#preview_button").click(function() {
				return ajax_getCensorPreview();
			});
		});
	// ]]></script>';
}