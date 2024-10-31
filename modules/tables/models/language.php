<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class LanguageModelPyt extends BaseObjectPyt {
	protected $_code = '';
	private $_langData = array();
	
	public function setCode( $code ) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}

	/**
	 * Returns an associative array of DataTables translation.
	 * The array keys is language name in browser.
	 * The array values is name of language for connecting of language data.
	 * @return array
	 */
    public function getLanguages( $lang = '' ) {
        $langs = array(
            'default' => 'default',
            'browser' => 'browser',
			'af' => 'Afrikaans',
			'sq' => 'Albanian',
			'Amharic',
			'ar' => 'Arabic',
			'hy' => 'Armenian',
			'az' => 'Azerbaijan',
			'bn' => 'Bangla',
			'eu' => 'Basque',
			'be' => 'Belarusian',
			'bg' => 'Bulgarian',
			'ca' => 'Catalan',
			'zh-TW' => 'Chinese-traditional',
			'zh' => 'Chinese',
			'hr' => 'Croatian',
			'cs' => 'Czech',
			'da' => 'Danish',
			'nl' => 'Dutch',
            //'English', // it is default language
			'et' => 'Estonian',
			'fil' => 'Filipino',
			'fi' => 'Finnish',
			'fr' => 'French',
			'gl' => 'Galician',
			'ka' => 'Georgian',
			'de' => 'German',
			'el' => 'Greek',
			'gu' => 'Gujarati',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'hu' => 'Hungarian',
			'is' => 'Icelandic',
			'Indonesian-Alternative',
			'id' => 'Indonesian',
			'ga' => 'Irish',
			'it' => 'Italian',
			'ja' => 'Japanese',
			'kk'=> 'Kazakh',
			'ko' => 'Korean',
			'ky' => 'Kyrgyz',
			'lv' => 'Latvian',
			'lt' => 'Lithuanian',
			'mk' => 'Macedonian',
			'ms' => 'Malay',
			'mn' => 'Mongolian',
			'ne' => 'Nepali',
			'nb' => 'Norwegian-Bokmal',
			'nn' => 'Norwegian-Nynorsk',
			'ps' => 'Pashto',
			'fa' => 'Persian',
			'pl' => 'Polish',
			'pt-BR' => 'Portuguese-Brasil',
			'pt' => 'Portuguese',
			'ro' => 'Romanian',
			'ru' => 'Russian',
			'sr' => 'Serbian',
			'si' => 'Sinhala',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'es' => 'Spanish',
			'sw' => 'Swahili',
			'sv' => 'Swedish',
			'ta' => 'Tamil',
			'te' => 'telugu',
			'te-IN' => 'telugu',
			'th' => 'Thai',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'ur' => 'Urdu',
			'uz' => 'Uzbek',
			'vi' => 'Vietnamese',
			'cy' => 'Welsh',
        );
		return empty($lang) ? $langs : ( isset($langs[$lang]) ? $langs[$lang] : false );
    }

    /**
     * Returns an array of the current languages at the official DataTable repo.
     * @return array|null
     */
    public function downloadLanguages()
    {
        $url = 'https://api.github.com/repos/DataTables/Plugins/contents/i18n';
        $languages = array();
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return null;
        }
        if (200 !== wp_remote_retrieve_response_code($response)) {
            return null;
        }
        $files = json_decode($response['body']);

        if (!is_array($files)) {
            return null;
        }
        foreach ($files as $file) {
            $languages[] = str_replace('.lang', '', $file->name);
        }

        return $languages;
    }

	/**
	 * Returns the list of translation data for all available languages.
	 * @return array|mixed
	 */
	public function getLanguagesData( $lang ) {
		if (empty($this->_langData)) {
			$this->_langData = include_once 'languagesData.php';
		}
		return isset($this->_langData[$lang]) ? json_decode($this->_langData[$lang], true) : array();
	}
}