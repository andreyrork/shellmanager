<?php

header('Content-Type: text/html; charset="utf-8"');

// Disable html tags from error messages
if (function_exists('ini_set')) {
	ini_set('html_errors', 'off');
}
ob_start();

// Глобальный массив с тегами, вставляемыми в контент.
$__tags = array(
	array('open' => '<p>', 'close' => '</p>'),
	array('open' => '<div>', 'close' => '</div>'),
	array('open' => '<em>', 'close' => '</em>'),
);

// -----------------------
// Константы, определяющие пути до файлов с внешним контентом.
// -----------------------
// Файл шаблона.
define('FILE_PATTERN', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pattern.txt');
// Большой файл с контентом.
define('FILE_CONTENT', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'content.txt');
// Файл лога.
define('FILE_LOG',     dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log.txt');

// Директория для хранения кешированных файлов. Должна быть доступна для записи.
define('PATH_CACHE',   dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache');

// -----------------------
// Константы, определяющие заменяемые теги в файле шаблона.
// -----------------------
define('PATTERN_TAG_TITLE', '<_TITLE_>');
define('PATTERN_TAG_CONTENT', '<_CONTENT_>');
define('PATTERN_TAG_KEYWORD', '<_KEYWORDS_>');

// Имя параметра в $_GET запросе, по которому передано ключевое слово.
define('PARAM_KEYWORD', 'search');
// Имя параметра, указывающего на отладочный режим.
define('PARAM_DEBUG', 'debug');

// Количество предложений, выбираемых из файла с контентом.
define("SENTENCE_NUMBER", 10);

define("METAWORD_NUMBER", 4);

// Отношение колличества тегов к колличеству предложений.
// Пример: при значении 2, будет 1 тег на 2 предложения.
define("TAG_FREQUENCY", 2);

// Максимальное колличество повторение ключевых слов в контенте (в процентах)
define('MAX_REPEAT_KEYWORD_PERCENT', 3);

// Минимальная длина предложения, берущегося из контента.
define('MIN_SENTENCE_LENGTH', 5);

define("MIN_METAWORD_LENGTH", 5);

// -----------------------
// Константы, определяющие уровень важности оповещений в логе.
// -----------------------
define('LOG_LEVEL_NOTICE', 'notice');
define('LOG_LEVEL_WARN',   'warn');
define('LOG_LEVEL_FATAL',  'fatal');

// -----------------------
// Константы, определяющие сообщения об ошибках.
// -----------------------
define('ERROR_FILE_NOT_EXISTS'       , "File '%s' is not exists."               );
define('ERROR_FILE_NOT_READABLE'     , "File '%s' is not readable."             );
define('ERROR_FILE_NOT_OPEN'         , "Unable to open file '%s'."              );
define('ERROR_FILE_EMPTY'            , "File '%s' is empty."                    );
define('ERROR_FILE_NOT_READ'         , "Unable to read file '%s'."              );
define('ERROR_FILE_NOT_WRITE'        , "Unable write to file '%s'."             );
define('ERROR_DIRECTORY_NOT_WRITABLE', "Directory '%s' is not writable"         );
define('ERROR_KEYWORD_EMPTY'         , "Keyword is empty"                       );
define('CACHE_NOT_FOUND'             , "Cached file for keyword '%s' not found" );


// -----------------------
//       FUNCTIONS
// -----------------------

/**
 * Возвращает используемое ключевое слово.
 * @return string
 */
function getKeyword()
{
	return isset($_GET[PARAM_KEYWORD]) ? $_GET[PARAM_KEYWORD] : '';
}

/**
 * Указывает, включен ли отладочный режим.
 * @return boolean
 */
function isDebugMode()
{
	return isset($_GET[PARAM_DEBUG]) ? true : false;
}

/**
 * Добавляет в лог информацию о ходе выполнения скрипта или ошибках.
 *
 * @param string $message
 * @param string $type OPTIONAL default LOG_LEVEL_NOTICE
 * @return void
 */
function logMessage($message, $type = LOG_LEVEL_NOTICE)
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$date = date('Y-m-d H:i:s', time());
	$message = "[$date] <$ip> ($type) $message\n";

	if ($file = fopen(FILE_LOG, 'a')) {
		if (fwrite($file, $message)) {
			fclose($file);
		}
	}

	if (isDebugMode()) {
		echo $message;
	}

	// В случае фатальной ошибки завершаем работу скрипта.
	if ($type == LOG_LEVEL_FATAL) {
		exit;
	}
}

/**
 * Возвращает содержимое файла по переданному пути.
 *
 * @param string $path
 * @return string
 */
function getContent($path, $logLevel = LOG_LEVEL_FATAL)
{
	// Check is file exists.
	if (!file_exists($path)) {
		logMessage(sprintf(ERROR_FILE_NOT_EXISTS, $path), $logLevel);
		return '';
	}

	// Check is file readable.
	if (!is_readable($path)) {
		logMessage(sprintf(ERROR_FILE_NOT_READABLE, $path), $logLevel);
		return '';
	}

	// Try to open file for read.
	$file = fopen($path, 'r');
	if (!$file) {
		logMessage(sprintf(ERROR_FILE_NOT_OPEN, $path), $logLevel);
	}

	// Check file size is not equals 0.
	if (!$filesize = filesize($path)) {
		logMessage(sprintf(ERROR_FILE_EMPTY, $path), $logLevel);
		return '';
	}

	// Try to read and check content is not empty.
	if (!$content = fread($file, $filesize)) {
		logMessage(sprintf(ERROR_FILE_NOT_READ, $path), $logLevel);
		return '';
	}

	// Close file.
	fclose($file);

	return $content;
}

/**
 * Возвращает значение из кеша по ключу.
 *
 * @param string $key
 * @return string
 */
function getCache($key)
{
	$path = PATH_CACHE . DIRECTORY_SEPARATOR . md5($key);
	return getContent($path, LOG_LEVEL_NOTICE);
}

/**
 * Записывает в кеш значение по ключу.
 *
 * @param string $key
 * @param string $value
 * @return boolean
 */
function setCache($key, $value) {
	// Check is cache dir writable.
	if (!is_writable(PATH_CACHE)) {
		logMessage(sprintf(ERROR_DIRECTORY_NOT_WRITABLE, constant(PATH_CACHE)), LOG_LEVEL_FATAL);
		return false;
	}

	// Path to cached file.
	$path = PATH_CACHE . DIRECTORY_SEPARATOR . md5($key);

	// Try to open file for write.
	$file = fopen($path, 'w');
	if (!$file) {
		logMessage(sprintf(ERROR_FILE_NOT_OPEN, $path), LOG_LEVEL_FATAL);
		return false;
	}

	// Try to write at file.
	if (!fwrite($file, $value)) {
		logMessage(sprintf(ERROR_FILE_NOT_WRITE, $path), LOG_LEVEL_FATAL);
		return false;
	}

	fclose($file);
	return true;
}

// ---------------------
//  НЕ ТРОГАТЬ. ОПАСНО.
// ---------------------



/**
 * Возвращает тег по передаваемой команде.
 *
 * @param string $option empty|random
 * @return array array('open' => %s, 'close' => %s)
 */
function getTag($option)
{
	global $__tags;

	if ($option == 'random') {
		$tag = rand(0, count($__tags)-1);
		return $__tags[$tag];
	}

	return array('open' => '', 'close' => '');
}

/**
 * Выбирает из переданного массива предложений одно случайное,
 * возвращает его и при этом удаляет из массива.
 *
 * @param array $sentences
 * @return string
 */
function getSentence(&$sentences)
{
	// Выбираем из массива предложений - одно случайное.
	$random = rand(0, count($sentences) - 1);
	$sentence = $sentences[$random];

	// Удаляем это предложение из общего массива, чтобы не было повторений.
	unset($sentences[$random]);
	sort($sentences);

	return $sentence;
}

/**
 * Из переданного контента выдирает предложения с длиной не меньше MIN_SENTENCE_LENGTH.
 *
 * @param string $content
 * @return array
 */
function getSentencesArray($content)
{
	$sentences = explode('.', $content);

	foreach ($sentences as $key => $sentence) {
		$sentences[$key] = trim($sentences[$key]);
		if (strlen($sentences[$key]) < MIN_SENTENCE_LENGTH) {
			unset($sentences[$key]);
		}
	}

	sort($sentences);
	return $sentences;
}

/**
 * Выполняет преобразование переданной строки:
 * 1. Из строки выбираются рандомные SENTENCE_NUMBER предложений.
 * 2. К предложениям в пропорции TAG_FREQUENCY добавляются теги.
 *
 * @param string $content
 * @return string
 */
function randomizeContent($content)
{

	$sentences = getSentencesArray($content);
	if(count($sentences) <= SENTENCE_NUMBER) {
		logMessage(ERROR_NOT_ENOUGH_SENTENCES_IN_CONTENT, LOG_LEVEL_FATAL);
	}


	$resultContent = '';
	for ($i = 0; $i < SENTENCE_NUMBER; $i++) {

		$sentence = getSentence($sentences);

		if (rand(1, TAG_FREQUENCY) == TAG_FREQUENCY) {
			$tag = getTag('random');
		} else {
			$tag = getTag('empty');
		}

		$resultContent .= sprintf("%s%s.%s<br />\n", $tag['open'], $sentence, $tag['close']);
	}

	return $resultContent;
}

/**
 * Оставляет в переданном тексте только MAX_REPEAT_KEYWORD_PERCENT процентов ключевых слов.
 *
 * @param string $content Контект, в котором проверяется наличие ключевых слов.
 * @param string $keyword Ключевое слово, проверяемое в контенте.
 * @return string
 */
function removeRepeatWords($content, $keyword)
{
	$wordCount = str_word_count($content);
	$keywordCount = substr_count(strtolower($content), strtolower($keyword));
	$percent = ($keywordCount / $wordCount) * 100;

	if ($percent >= MAX_REPEAT_KEYWORD_PERCENT) {
		$deleteCount = round(($percent - MAX_REPEAT_KEYWORD_PERCENT) * $wordCount / 100) + 1;
		$pattern = "~$keyword~is";
		$content = preg_replace($pattern, '', $content,  $deleteCount);
	}

	return $content;
}

/**
 * Формирует массив наиболее часто используемых слов в формате array(word => count)
 *
 * @param string $content
 * @return array
 */
function mostUsableWords($content)
{
	$words = explode(' ', $content);

	foreach ($words as $word) {
		$count = substr_count(strtolower($content), strtolower($word));
		$wordscount[$word] = $count;
	}

	arsort($wordscount);
	return $wordscount;
}

/**
 * Формирует строку из массива наиболее часто используемых слов, выбирая METAWORD_NUMBER слов
 * длиной >= MIN_METAWORD_LENGTH.
 *
 * @param array $words
 * @return string
 */
function formMetaWords($words) {
	$counter = 0;
	$result = '';
	foreach ($words as $key => $number) {
		if ($counter >= METAWORD_NUMBER) {
			break;
		}

		if (strlen($key) >= MIN_METAWORD_LENGTH) {
			if ($counter) {
				$result .= ', ';
			}

			$result .= $key;
			$counter++;
		}
	}

	return $result;
}

// -----------------------
//   PROCESS KEYWORD
// -----------------------

// Получаем ключевое слово.
$keyword = getKeyword();

// Проверка существования кейворда.
if (!$keyword) {
	logMessage(ERROR_KEYWORD_EMPTY, LOG_LEVEL_FATAL);
}

// Проверяем, есть ли для него сгенеренная страничка в кеше.
if (!($pattern = getCache($keyword)) || isDebugMode()) {

	logMessage(sprintf(CACHE_NOT_FOUND, $keyword), LOG_LEVEL_NOTICE);

	$content = getContent(FILE_CONTENT);
	$content = randomizeContent($content);
	$content = removeRepeatWords($content, $keyword);

	$words = mostUsableWords($content);
	$metakeywords = formMetaWords($words);


	$pattern = getContent(FILE_PATTERN);
	$pattern = str_replace(PATTERN_TAG_KEYWORD, $metakeywords, $pattern);
	$pattern = str_replace(PATTERN_TAG_TITLE, $keyword, $pattern);
	$pattern = str_replace(PATTERN_TAG_CONTENT, $content, $pattern);

	setCache($keyword, $pattern);
}

// Логгируем всю информацию, которая попыталась оказаться на экране. В основном для отлова ошибок.
$buffer = ob_get_contents();
if ($buffer && !isDebugMode()) {
	ob_end_clean();
	logMessage("<pre>$buffer</pre>\n", LOG_LEVEL_NOTICE);
}

echo $pattern;


// @todo Доделать функцию для составления meta keyword
