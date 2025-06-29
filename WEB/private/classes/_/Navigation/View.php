<?php namespace _\Navigation;

class View {
	
	private static $viewSyntax = [
		'tagPattern' => ['l' => '<!--\s*\{\{', 'r' => '\}\}\s*-->'],
		'placeholderPattern' => ['l' => '\{\{', 'r' => '\}\}', 'name' => '%?[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*'],
		'subLabelSeparator' => '.',
		'instancePattern' => ['l' => '%', 'r' => '', 'key' => '#', 'value' => '@'],
		'argumentPattern' => '\([a-zA-Z0-9_\/]+\)',
		'tagOpening' => ':',
		'tagClosing' => ';'
	];
	private static $viewTagName = 'VIEW';
	private static $foreachTagName = 'FOREACH';
	private static $absolutePathTagName = 'ABS';

	public static function retrieve(string $pViewName, array $pViewValues = [], bool $pfReturn = false) : bool|string {
		
		$viewPath = realpath(VIEW_DIR . str_replace('/', DIR_SEP, $pViewName) . '.' . VIEW_EXTENSION);
		if(!$viewPath) return false;
		
		$viewFileString = self::minifyHTML(file_get_contents($viewPath));
		$viewTag = self::find(self::$viewTagName, $viewFileString, 2)[0] ?? null;
		$viewString =
			(empty($viewTag)) ?
			$viewFileString :
			self::parseTag($viewFileString, $viewTag['str'], $viewTag['pos'])['content']
		;
		
		self::resolveSubViewTags($viewString, $pViewValues);
		self::resolveLoopTags($viewString, $pViewValues);
		self::resolveAbsolutePathTags($viewString, dirname($viewPath));
		self::resolvePlaceholders($viewString, $pViewValues);

		if($pfReturn) {
			return $viewString;
		} else {
			echo $viewString;
			return true;
		}
		
	}

	private static function composeTag(string $pSignature): string {

		$tagPattern =
			'/' .
			self::$viewSyntax['tagPattern']['l'] .
			$pSignature .
			self::$viewSyntax['tagPattern']['r'] .
			'/'
		;
		return $tagPattern;

	}

	private static function composePlaceholder(string $pPlaceholder, bool $fRegex = true): string {

		if($fRegex) {
			$placeholderPattern =
				'/' .
				self::$viewSyntax['placeholderPattern']['l'] .
				$pPlaceholder .
				self::$viewSyntax['placeholderPattern']['r'] .
				'/'
			;
		} else {
			$placeholderPattern = stripslashes (
				self::$viewSyntax['placeholderPattern']['l'] .
				$pPlaceholder .
				self::$viewSyntax['placeholderPattern']['r']
			);
		}
		return $placeholderPattern;

	}
	
	private static function minifyHTML(string $pHTMLString): string {
		$replace = [
			'/\n/' => ' ',
			'/\s+/' => ' ',
			'/\>\s+\</' => '><'
		];

		return preg_replace(array_keys($replace), array_values($replace), $pHTMLString);
	}

	private static function find(string $pElement, string $pViewString, int $pType = 0, bool $pfArguments = false): ?array {
		
		/*
		 * Types:
		 * 0: placeholder
		 * 1: simple tag
		 * 2: opening tag
		 * 3: closing tag
		 */

		$element = trim($pElement);

		if($pType == 0) { # Placeholder

			$placeholder = $element;
			$pattern = self::composePlaceholder($placeholder);
			
			
		} else { # Tag

			$signature = $element;

			if($pfArguments) {
				$signature .= self::$viewSyntax['argumentPattern'];
			}

			switch($pType) {
				case 2: # Opening tag
					$signature .= self::$viewSyntax['tagOpening'];
					break;
				case 3: # Closing tag
					$signature .= self::$viewSyntax['tagClosing'];
					break;
				default:
					null;
			}

			$pattern = self::composeTag($signature);

		}

		preg_match_all($pattern, $pViewString, $matched, PREG_OFFSET_CAPTURE);
		
		if(!empty($matched[0])) {
			return array_map (
				function($pMatchData) {
					return [
						'str' => $pMatchData[0],
						'pos' => $pMatchData[1]
					];
				},
				$matched[0]
			);
		} else {
			return [];
		}
		
	}
	
	private static function parseTag(string $pString, string $pTag, int $pTagStart): array {
		
		$tag = $pTag;
		$tagStart = $pTagStart;
		$tagLength = strlen($tag);
		$tagEnd = $tagStart + $tagLength;
		$tagSignature = trim (
			preg_replace (
				[
					'/' . self::$viewSyntax['tagPattern']['l'] . '/',
					'/' . self::$viewSyntax['tagPattern']['r'] . '/'
				],
				['',''],
				$tag
			)
		);

		$argumentStart = (strpos($tag, '(')) ? strpos($tag, '(') + 1 : 0;
		$argumentLength = ($argumentStart) ? strpos($tag, ')') -  $argumentStart : 0;
		$argument = ($argumentLength > 0) ? substr($tag, $argumentStart, $argumentLength) : null;
		
		if(substr($tagSignature, -1) == self::$viewSyntax['tagOpening']) {
			$closingTagStart = strpos($pString, str_replace(self::$viewSyntax['tagOpening'], self::$viewSyntax['tagClosing'], $tag), $tagEnd);
			$closingTagEnd = $closingTagStart + $tagLength;
			$content = trim(substr($pString, $tagEnd, $closingTagStart - $tagEnd));
		}

		$tagData = [
			'tag' => $tag,
			'tagLength' => $tagLength,
			'tagStart' => $tagStart,
			'tagEnd' => $tagEnd,
			'closingTagStart' => $closingTagStart ?? null,
			'closingTagEnd' => $closingTagEnd ?? null,
			'argument' => $argument,
			'content' => $content ?? null
		];
		
		return $tagData;
		
	}
	
	private static function resolvePlaceholders(string &$pViewString, array $pValues, int $pMode = 0): bool {

		switch($pMode) {

			case 0: # From matched label to values

				$pattern = self::$viewSyntax['placeholderPattern']['name'];
				$placeholders = self::find($pattern, $pViewString);
				
				foreach($placeholders as $placeholder) {
					
					$placeholder = $placeholder['str'];
					$label = str_replace(['{', '}'], ['', ''], $placeholder);
					
					foreach(explode(self::$viewSyntax['subLabelSeparator'], $label) as $key) {
						$value = $value[$key] ?? $pValues[$key] ?? '';
					}
					if(!is_array($value)) {
						$pViewString = str_replace(self::composePlaceholder($label, false), $value, $pViewString);
					}
					
				}
				break;

			case 1: # From values to labels

				foreach($pValues as $placeholder => $value) {
				
					if(!is_array($value)) {
						$pViewString = str_replace(self::composePlaceholder($placeholder, false), ($value ?? ''), $pViewString);
					}
					
				}
				
				break;
			
		}

		return true;

	}
	
	private static function resolveSubViewTags(string &$pViewString, array $pValues): bool {

		$subViewTags = self::find(self::$viewTagName, $pViewString, 1, true);
		
		if(!empty($subViewTags)) {

			$offset = 0;
			foreach($subViewTags as $subViewTag) {
				
				$tagData = self::parseTag($pViewString, $subViewTag['str'], $subViewTag['pos'] + $offset);
				$subViewName = $tagData['argument'];
				$subViewString = self::retrieve($subViewName, $pValues, true);
				$pViewString = substr($pViewString, 0, $tagData['tagStart']) . $subViewString . substr($pViewString, $tagData['tagEnd']);
				$offsetAdjustment = strlen($subViewString) - $tagData['tagLength'];
				$offset += $offsetAdjustment;

			}

		}

		return true;

	}

	private static function resolveAbsolutePathTags(string &$pViewString, string $pViewDir): bool {

		$absolutePathTags = self::find(self::$absolutePathTagName, $pViewString, 2);
		
		$offset = 0;
		foreach($absolutePathTags as $absolutePathTag) {
			
			$tagData = self::parseTag($pViewString, $absolutePathTag['str'], $absolutePathTag['pos'] + $offset);
			
			preg_match('/(href|src)=("|\')([a-zA-Z0-9_\-\.\/]+)("|\')/', $tagData['content'], $match, PREG_OFFSET_CAPTURE);
			if(empty($match[3]))
				continue;
			
			$relPathStr = $match[3][0];
			$relPathPos = $match[3][1];
			
			$viewURI = str_replace(DIR_SEP, '/', str_replace(realpath(VIEW_DIR) . DIR_SEP, PAGE_URI_ROOT, $pViewDir)) . '/';
			$absolutePathString = $viewURI . $relPathStr;
			$newTag = substr($tagData['content'], 0, $relPathPos) . $absolutePathString . substr($tagData['content'], $relPathPos + strlen($relPathStr));
			$pViewString = substr($pViewString, 0, $tagData['tagStart']) . $newTag . substr($pViewString, $tagData['closingTagEnd']);
			$offsetAdjustment = (strlen($absolutePathString) - strlen($relPathStr)) - (($tagData['tagLength']) * 2);
			$offset += $offsetAdjustment;
			
		}

		return true;

	}

	private static function resolveLoopTags(string &$pViewString, array $pValues): bool {

		$foreachTags = self::find(self::$foreachTagName, $pViewString, 2, true);
		
		$offset = 0;
		foreach($foreachTags as $foreachTag) {
			
			$tagData = self::parseTag($pViewString, $foreachTag['str'], $foreachTag['pos'] + $offset);
			$valueKey = $tagData['argument'];
			$foreachValues = $pValues[$valueKey] ?? [];
			
			self::expandLoopTag($tagData, $foreachValues, $pViewString, $offset);
			
		}

		return true;

	}

	private static function expandLoopTag(array $pTagData, array $pValues, string &$pViewString, int &$pOffset): bool {	
		
		$instanceKeyPlaceholder = self::$viewSyntax['instancePattern']['l'] . self::$viewSyntax['instancePattern']['key'] . self::$viewSyntax['instancePattern']['r'];
		$instanceValuePlaceholder = self::$viewSyntax['instancePattern']['l'] . self::$viewSyntax['instancePattern']['value'] . self::$viewSyntax['instancePattern']['r'];

		$content = $pTagData['content'];
		$foreachValues = $pValues;
					
		$repeatedString = '';
		foreach($foreachValues as $foreachKey => $foreachValue) {
			
			/*
			 * Substitution of the key
			 * Here the new instance is appended to the repeated string,
			 * and the next replacements are applied to $repeatedString
			 */
			$contentInstance = $content;
			$placeholderValue = [$instanceKeyPlaceholder => $foreachKey];
			self::resolvePlaceholders($contentInstance, $placeholderValue, 1);
			
			/*
			 * Substitution of the value
			 * The placeholders are substituted using the method resolvePlaceholders().
			 * If there's a single value, the array passed to the method
			 * has just one element, that maps the placeholder of the instance value
			 * to the actual value ($foreachValue). If the value is itself an array,
			 * then this array is passed to the method after the bare names of the
			 * keys are translated into the instance syntax of the view tag.
			 */
			if(is_array($foreachValue)) {
				$placeholderValues = [];
				foreach($foreachValue as $k => $v) {
					$placeholderValues[self::$viewSyntax['instancePattern']['l'] . $k . self::$viewSyntax['instancePattern']['r']] = $v;
				}
			} else {
				$placeholderValues = [$instanceValuePlaceholder => $foreachValue];
			}
			self::resolvePlaceholders($contentInstance, $placeholderValues, 1);
			
			$repeatedString .= $contentInstance;

		}

		$pViewString = substr($pViewString, 0, $pTagData['tagStart']) . $repeatedString . substr($pViewString, $pTagData['closingTagEnd']);
		$offsetAdjustment = (strlen($repeatedString) - strlen($content)) - (($pTagData['tagLength']) * 2);
		$pOffset += $offsetAdjustment;

		return true;
		
	}

}

?>
