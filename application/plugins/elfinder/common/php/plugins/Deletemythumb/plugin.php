<?php
/**
 * elFinder Plugin Deletemythumb
 *
 * Удаляет превьюшки разного размера удаляемого файла
 *
 * @author Budnikov Sergey
 */
class elFinderPluginDeletemythumb
{
	private $opts = array();

	public function __construct($opts) {
		$this->opts = $opts;
	}

	public function cmdPreprocess($cmd, &$args, $elfinder, $volume) {
		$CI = &get_instance();

		$filesDeleted = array();

		foreach($args['removed'] as $key => $fileInfo) {
			$fileUrl = str_replace(APP_BASE_PATH, '', $fileInfo['realpath']);
			$thumbPreName = $CI->ElfinderModel->thumbFileName($fileUrl);
			$filesDeleted[] = $thumbPreName;
		}

		$thumbPath = APP_BASE_PATH . $CI->ElfinderModel->thumbPath;
		$dirItr = new DirectoryIterator($thumbPath);
		foreach($dirItr as $file) {
			$fileThumbName = $file->getFilename();
			foreach($filesDeleted as $val) {
				if($this->stripos($fileThumbName, $val) === 0) {
					if(file_exists($thumbPath . $fileThumbName)) {
						unlink($thumbPath . $fileThumbName);
					}
				}
			}
		}

		return true;
	}

	private function stripos($haystack , $needle , $offset = 0) {
		if (function_exists('mb_stripos')) {
			return mb_stripos($haystack , $needle , $offset, 'UTF-8');
		} else if (function_exists('mb_strtolower') && function_exists('mb_strpos')) {
			return mb_strpos(mb_strtolower($haystack, 'UTF-8'), mb_strtolower($needle, 'UTF-8'), $offset);
		}
		return stripos($haystack , $needle , $offset);
	}
}
