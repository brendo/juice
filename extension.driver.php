<?php

	/**
	 * @package juice
	 */

	class Extension_Juice extends Extension {

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitialiseAdminPageHead',
					'callback' => 'minifyAssets'
				)
			);
		}

		public function install() {
			$this->uninstall();

			return true;
		}

		public function update($previousVersion = false) {
			return true;
		}

		public function uninstall() {
			if(self::doesCacheExist()) {
				General::deleteFile(self::$styles_filename);
				General::deleteFile(self::$scripts_filename);
			}

			return true;
		}

		public static function css() {
			return CACHE . '/styles.min.css';
		}

		public static function js() {
			return CACHE . '/scripts.min.js';
		}

		public static function doesCacheExist() {
			return (is_file(self::css()) && is_file(self::js()));
		}

		public function minifyAssets(array $context) {
			$existing_cache = self::doesCacheExist();

			$head_elements = Administration::instance()->Page->Head();
			$styles = $scripts = array();
			$style = $script = '';

			foreach($head_elements as $index => $element) {
				if($element->getAttribute('type') == 'text/css') {
					if(!$existing_cache) {
						$styles[] = $element->getAttribute('href');
						$style .= file_get_contents($element->getAttribute('href'));
					}

					Administration::instance()->Page->removeFromHeadByPosition($index);
				}

				if($element->getAttribute('type') == 'text/javascript') {
					if($element->getAttribute('src')) {
						if(!$existing_cache) {
							$scripts[] = $element->getAttribute('src');
							$script .= file_get_contents($element->getAttribute('src'));
						}

						Administration::instance()->Page->removeFromHeadByPosition($index);
					}

				}
			}

			if(!$existing_cache) {
				file_put_contents(self::css(), $style);
				file_put_contents(self::js(), $script);
			}

			Administration::instance()->Page->addStylesheetToHead(URL . '/manifest/cache/styles.min.css', 'all', 1);
			Administration::instance()->Page->addScriptToHead(URL . '/manifest/cache/scripts.min.js', 2);
		}

	}