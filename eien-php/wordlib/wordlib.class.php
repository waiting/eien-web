<?php
/**	�ʿ���
	�ʿ�����Ǳ��ر���,��GB��
	���ṩ�ִʲ��� */

define('EIEN_WORDLIB_CLASS', 'wordlib/wordlib.class.php');

class WordLib
{
private $fpWordLib;
public $wordFileSize;
public $wordsCount;
public $wordItemSize;
public static function str_match($word, $text)
{
	$lengthWord = strlen($word);
	$lengthText = strlen($text);
	for ($i = 0; $i < $lengthWord && $i < $lengthText; $i++)
	{
		if (ord($word[$i]) > ord($text[$i]))
		{
			return 1;
		}
		else if (ord($word[$i]) < ord($text[$i]))
		{
			return -1;
		}
	}
	if ($i == $lengthWord)
	{
		return 0;
	}
	else // $i == $lengthText
	{
		return 1;
	}
}
public function __construct($libFile)
{
	$this->fpWordLib = fopen($libFile, 'rb');
	if ($this->fpWordLib)
	{
		// �ʿ��������
		$this->wordFileSize = filesize($libFile);
		$this->wordItemSize = 32;
		$this->wordsCount = (int)($this->wordFileSize / $this->wordItemSize);

	}
}
public function __destruct()
{
	if ($this->fpWordLib) fclose($this->fpWordLib);
}
public function findWord($word, $first, $last)
{
	if ($this->fpWordLib)
	{
		while ($first <= $last)
		{
			$mid = (int)($first + ($last - $first) / 2);
			$cmp = strcmp($this->wordAt($mid), $word);
			if ($cmp == 0)
			{
				return $mid;
			}
			else if ($cmp < 0)
			{
				$first = $mid + 1;
			}
			else
			{
				$last = $mid - 1;
			}
		}
	}
	return -1;
}
public function findWordEx($word, &$count, $first, $last)
{
	$count = 0;
	if (!$this->fpWordLib)
	{
		return -1;
	}
	// ����,����ƥ��lpszWord��һ���ʵ�λ��
	$pos_match = -1;
	while ($first <= $last)
	{
		$mid = (int)($first + ($last - $first) / 2);
		$cmp = self::str_match($word, $this->wordAt($mid));
		if ($cmp == 0)
		{
			$pos_match = $mid;
			break;
		}
		else if ($cmp < 0)
		{
			$last = $mid - 1;
		}
		else
		{
			$first = $mid + 1;
		}
	}
	// �ѵ�
	if ($pos_match != -1)
	{
		$text = '';
		// ��ǰ
		$this->wordSeek($pos_match, SEEK_SET);
		$pos = $pos_match;
		$prevCount = 0;
		if ($pos > 0)
		{
			$text = $this->wordPrev();
			$pos--;
			while (self::str_match($word, $text) == 0)
			{
				$prevCount++;
				if ($pos > 0)
				{
					$text = $this->wordPrev();
					$pos--;
				}
				else
				{
					break;
				}
			}
		}
		// ���
		$this->wordSeek($pos_match, SEEK_SET);

		$pos = $pos_match;
		$nextCount = 0;
		if ($pos < $this->wordsCount - 1)
		{
			$text = $this->wordNext();
			$pos++;
			while (self::str_match($word, $text) == 0)
			{
				$nextCount++;
				if ($pos < $this->wordsCount - 1)
				{
					$text = $this->wordNext();
					$pos++;
				}
				else
				{
					break;
				}
			}
		}

		$count = 1 + $prevCount + $nextCount;
		$pos_match -= $prevCount;
	}

	return $pos_match;
}
public function wordAt($pos)
{
	return $this->wordSeek($pos, SEEK_SET);
}
public function wordSeek($offset, $rel = SEEK_CUR)
{
	$word = '';
	if ($this->fpWordLib)
	{
		fseek($this->fpWordLib, $offset * $this->wordItemSize, $rel);
		$word = fread($this->fpWordLib, $this->wordItemSize);
		fseek($this->fpWordLib, -1 * $this->wordItemSize, SEEK_CUR);
	}
	return trim($word);
}
public function wordNext($n = 1)
{
	return $this->wordSeek($n, SEEK_CUR);
}
public function wordPrev($n = 1)
{
	return $this->wordSeek(-$n, SEEK_CUR);
}
public function splitWord($text, &$arrWords)
{
	$len = strlen($text);
	$str = "";	//�浱ǰ�ַ���
	$strMat = "";	//��ƥ���ַ���

	$haveMatch = -1;	// �ѵ���λ��
	$cch = 0;	// �������ӵ��ַ���
	$first = 0;
	$last = $this->wordsCount - 1;
	for ($i = 0; $i < $len;)
	{
		$ch = $text[$i];
		$cch = 1;
		if (ord($text[$i]) & 0x80)
		{
			$i++;
			$ch .= $text[$i];
			$cch = 2;
		}
		$str .= $ch; // ��ǰ�ַ���

		$pos = -1;
		$count = 0;
		$pos = $this->findWordEx($str, $count, $first, $last);
		if ($pos != -1)
		{
			$first = $pos;
			$last = $pos + $count - 1;
			$haveMatch = $pos;
			$strMat = $str;
			$i++;
		}
		else // �Ѳ���
		{
			if ($haveMatch != -1) // ��ǰ��ƥ��
			{
				if ($this->wordAt($haveMatch) == $strMat)
				{
					array_push($arrWords, $strMat);
				}
				$haveMatch = -1;
				// �˻�ȥ
				$i -= $cch - 1;
			}
			else // ��ǰ��ƥ��
			{
				$en_str = '';
				while ($i < $len && preg_match('/[_\\.0-9A-Za-z]/', $text[$i]))
				{
					$en_str .= $text[$i];
					$i++;
				}
				if ($en_str == '') $i++;
				else
				{
					array_push($arrWords, $en_str);
				}
			}
			$first = 0;
			$last = $this->wordsCount - 1;
			$str = "";
		}
	}
	// ����ѭ�����ж�һ��
	if ($haveMatch != -1)
	{
		if ($this->wordAt($haveMatch) == $strMat)
		{
			array_push($arrWords, $strMat);
		}
		$haveMatch = -1;
	}

	return count($arrWords);
}

}
