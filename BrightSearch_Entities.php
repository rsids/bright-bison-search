<?php
namespace BrightSearch;

class Entities {
	public static $namedentities = array ('&quot;' => '"', '&apos;' => '\'', '&amp;' => '&', '&lt;' => '<', '&gt;' => '>', '&nbsp;' => '', '&iexcl;' => '¡', '&cent;' => '¢',
										'&pound;' => '£', '&curren;' => '¤', '&yen;' => '¥', '&brvbar;' => '¦', '&sect;' => '§', '&uml;' => '¨', '&copy;' => '©', '&ordf;' => 'ª',
										'&laquo;' => '«', '&not;' => '¬', '&shy;' => '', '&reg;' => '®', '&macr;' => '¯', '&deg;' => '°', '&plusmn;' => '±', '&sup2;' => '²',
										'&sup3;' => '³', '&acute;' => '´', '&micro;' => 'µ', '&para;' => '¶', '&middot;' => '•', '&cedil;' => '¸', '&sup1;' => '¹', '&ordm;' => 'º',
										'&raquo;' => '»', '&frac14;' => '¼', '&frac12;' => '½', '&frac34;' => '¾', '&iquest;' => '¿', '&times;' => '×', '&divide;' => '÷',
										'&Agrave;' => 'À', '&Aacute;' => 'Á', '&Acirc;' => 'Â', '&Atilde;' => 'Ã', '&Auml;' => 'Ä', '&Aring;' => 'Å', '&AElig;' => 'Æ',
										'&Ccedil;' => 'Ç', '&Egrave;' => 'È', '&Eacute;' => 'É', '&Ecirc;' => 'Ê', '&Euml;' => 'Ë', '&Igrave;' => 'Ì', '&Iacute;' => 'Í',
										'&Icirc;' => 'Î', '&Iuml;' => 'Ï', '&ETH;' => 'Ð', '&Ntilde;' => 'Ñ', '&Ograve;' => 'Ò', '&Oacute;' => 'Ó', '&Ocirc;' => 'Ô', '&Otilde;' => 'Õ',
										'&Ouml;' => 'Ö', '&Oslash;' => 'Ø', '&Ugrave;' => 'Ù', '&Uacute;' => 'Ú', '&Ucirc;' => 'Û', '&Uuml;' => 'Ü', '&Yacute;' => 'Ý', '&THORN;' => 'Þ',
										'&szlig;' => 'ß', '&agrave;' => 'à', '&aacute;' => 'á', '&acirc;' => 'â', '&atilde;' => 'ã', '&auml;' => 'ä', '&aring;' => 'å', '&aelig;' => 'æ',
										'&ccedil;' => 'ç', '&egrave;' => 'è', '&eacute;' => 'é', '&ecirc;' => 'ê', '&euml;' => 'ë', '&igrave;' => 'ì', '&iacute;' => 'í', '&icirc;' => 'î',
										'&iuml;' => 'ï', '&eth;' => 'ð', '&ntilde;' => 'ñ', '&ograve;' => 'ò', '&oacute;' => 'ó', '&ocirc;' => 'ô', '&otilde;' => 'õ', '&ouml;' => 'ö',
										'&oslash;' => 'ø', '&ugrave;' => 'ù', '&uacute;' => 'ú', '&ucirc;' => 'û', '&uuml;' => 'ü', '&yacute;' => 'ý', '&thorn;' => 'þ', '&yuml;' => 'ÿ',
										'&forall;' => '∀', '&part;' => '∂', '&exist;' => '∃', '&empty;' => '∅', '&nabla;' => '∇', '&isin;' => '∈', '&notin;' => '∉', '&ni;' => '∋',
										'&prod;' => '∏', '&sum;' => '∑', '&minus;' => '−', '&lowast;' => '∗', '&radic;' => '√', '&prop;' => '∝', '&infin;' => '∞', '&ang;' => '∠',
										'&and;' => '∧', '&or;' => '∨', '&cap;' => '∩', '&cup;' => '∪', '&int;' => '∫', '&there4;' => '∴', '&sim;' => '∼', '&cong;' => '≅', '&asymp;' => '≈',
										'&ne;' => '≠', '&equiv;' => '≡', '&le;' => '≤', '&ge;' => '≥', '&sub;' => '⊂', '&sup;' => '⊃', '&nsub;' => '⊄', '&sube;' => '⊆', '&supe;' => '⊇',
										'&oplus;' => '⊕', '&otimes;' => '⊗', '&perp;' => '⊥', '&sdot;' => '⋅', '&Alpha;' => 'Α', '&Beta;' => 'Β', '&Gamma;' => 'Γ', '&Delta;' => 'Δ',
										'&Epsilon;' => 'Ε', '&Zeta;' => 'Ζ', '&Eta;' => 'Η', '&Theta;' => 'Θ', '&Iota;' => 'Ι', '&Kappa;' => 'Κ', '&Lambda;' => 'Λ', '&Mu;' => 'Μ',
										'&Nu;' => 'Ν', '&Xi;' => 'Ξ', '&Omicron;' => 'Ο', '&Pi;' => 'Π', '&Rho;' => 'Ρ', '&Sigma;' => 'Σ', '&Tau;' => 'Τ', '&Upsilon;' => 'Υ', '&Phi;' => 'Φ',
										'&Chi;' => 'Χ', '&Psi;' => 'Ψ', '&Omega;' => 'Ω', '&alpha;' => 'α', '&beta;' => 'β', '&gamma;' => 'γ', '&delta;' => 'δ', '&epsilon;' => 'ε',
										'&zeta;' => 'ζ', '&eta;' => 'η', '&theta;' => 'θ', '&iota;' => 'ι', '&kappa;' => 'κ', '&lambda;' => 'λ', '&mu;' => 'μ', '&nu;' => 'ν', '&xi;' => 'ξ',
										'&omicron;' => 'ο', '&pi;' => 'π', '&rho;' => 'ρ', '&sigmaf;' => 'ς', '&sigma;' => 'σ', '&tau;' => 'τ', '&upsilon;' => 'υ', '&phi;' => 'φ',
										'&chi;' => 'χ', '&psi;' => 'ψ', '&omega;' => 'ω', '&thetasym;' => 'ϑ', '&upsih;' => 'ϒ', '&piv;' => 'ϖ', '&OElig;' => 'Œ', '&oelig;' => 'œ',
										'&Scaron;' => 'Š', '&scaron;' => 'š', '&Yuml;' => 'Ÿ', '&fnof;' => 'ƒ', '&circ;' => 'ˆ', '&tilde;' => '˜', '&ensp;' => ' ', '&emsp;' => ' ',
										'&thinsp;' => ' ', '&zwnj;' => '', '&zwj;' => '', '&lrm;' => '‎', '&rlm;' => '‏', '&ndash;' => '–', '&mdash;' => '—', '&lsquo;' => '‘', '&rsquo;' => '’',
										'&sbquo;' => '‚', '&ldquo;' => '“', '&rdquo;' => '”', '&bdquo;' => '„', '&dagger;' => '†', '&Dagger;' => '‡', '&bull;' => '•', '&hellip;' => '…',
										'&permil;' => '‰', '&prime;' => '′', '&Prime;' => '″', '&lsaquo;' => '‹', '&rsaquo;' => '›', '&oline;' => '‾', '&euro;' => '€', '&trade;' => '™',
										'&larr;' => '←', '&uarr;' => '↑', '&rarr;' => '→', '&darr;' => '↓', '&harr;' => '↔', '&crarr;' => '↵', '&lceil;' => '⌈', '&rceil;' => '⌉', '&lfloor;' => '⌊',
										'&rfloor;' => '⌋', '&loz;' => '◊', '&spades;' => '♠', '&clubs;' => '♣', '&hearts;' => '♥', '&diams;' => '♦');
	public static $numericentities = array('&#34;' => '"', '&#39;' => '\'', '&#38;' => '&', '&#60;' => '<', '&#62;' => '>', '&#160;' => '', '&#161;' => '¡', '&#162;' => '¢', '&#163;' => '£',
										'&#164;' => '¤', '&#165;' => '¥', '&#166;' => '¦', '&#167;' => '§', '&#168;' => '¨', '&#169;' => '©', '&#170;' => 'ª', '&#171;' => '«', '&#172;' => '¬',
										'&#173;' => '', '&#174;' => '®', '&#175;' => '¯', '&#176;' => '°', '&#177;' => '±', '&#178;' => '²', '&#179;' => '³', '&#180;' => '´', '&#181;' => 'µ',
										'&#182;' => '¶', '&#183;' => '•', '&#184;' => '¸', '&#185;' => '¹', '&#186;' => 'º', '&#187;' => '»', '&#188;' => '¼', '&#189;' => '½', '&#190;' => '¾',
										'&#191;' => '¿', '&#215;' => '×', '&#247;' => '÷', '&#192;' => 'À', '&#193;' => 'Á', '&#194;' => 'Â', '&#195;' => 'Ã', '&#196;' => 'Ä', '&#197;' => 'Å',
										'&#198;' => 'Æ', '&#199;' => 'Ç', '&#200;' => 'È', '&#201;' => 'É', '&#202;' => 'Ê', '&#203;' => 'Ë', '&#204;' => 'Ì', '&#205;' => 'Í', '&#206;' => 'Î',
										'&#207;' => 'Ï', '&#208;' => 'Ð', '&#209;' => 'Ñ', '&#210;' => 'Ò', '&#211;' => 'Ó', '&#212;' => 'Ô', '&#213;' => 'Õ', '&#214;' => 'Ö', '&#216;' => 'Ø',
										'&#217;' => 'Ù', '&#218;' => 'Ú', '&#219;' => 'Û', '&#220;' => 'Ü', '&#221;' => 'Ý', '&#222;' => 'Þ', '&#223;' => 'ß', '&#224;' => 'à', '&#225;' => 'á',
										'&#226;' => 'â', '&#227;' => 'ã', '&#228;' => 'ä', '&#229;' => 'å', '&#230;' => 'æ', '&#231;' => 'ç', '&#232;' => 'è', '&#233;' => 'é', '&#234;' => 'ê',
										'&#235;' => 'ë', '&#236;' => 'ì', '&#237;' => 'í', '&#238;' => 'î', '&#239;' => 'ï', '&#240;' => 'ð', '&#241;' => 'ñ', '&#242;' => 'ò', '&#243;' => 'ó',
										'&#244;' => 'ô', '&#245;' => 'õ', '&#246;' => 'ö', '&#248;' => 'ø', '&#249;' => 'ù', '&#250;' => 'ú', '&#251;' => 'û', '&#252;' => 'ü', '&#253;' => 'ý',
										'&#254;' => 'þ', '&#255;' => 'ÿ', '&#8704;' => '∀', '&#8706;' => '∂', '&#8707;' => '∃', '&#8709;' => '∅', '&#8711;' => '∇', '&#8712;' => '∈', '&#8713;' => '∉',
										'&#8715;' => '∋', '&#8719;' => '∏', '&#8721;' => '∑', '&#8722;' => '−', '&#8727;' => '∗', '&#8730;' => '√', '&#8733;' => '∝', '&#8734;' => '∞', '&#8736;' => '∠',
										'&#8743;' => '∧', '&#8744;' => '∨', '&#8745;' => '∩', '&#8746;' => '∪', '&#8747;' => '∫', '&#8756;' => '∴', '&#8764;' => '∼', '&#8773;' => '≅', '&#8776;' => '≈',
										'&#8800;' => '≠', '&#8801;' => '≡', '&#8804;' => '≤', '&#8805;' => '≥', '&#8834;' => '⊂', '&#8835;' => '⊃', '&#8836;' => '⊄', '&#8838;' => '⊆', '&#8839;' => '⊇',
										'&#8853;' => '⊕', '&#8855;' => '⊗', '&#8869;' => '⊥', '&#8901;' => '⋅', '&#913;' => 'Α', '&#914;' => 'Β', '&#915;' => 'Γ', '&#916;' => 'Δ', '&#917;' => 'Ε',
										'&#918;' => 'Ζ', '&#919;' => 'Η', '&#920;' => 'Θ', '&#921;' => 'Ι', '&#922;' => 'Κ', '&#923;' => 'Λ', '&#924;' => 'Μ', '&#925;' => 'Ν', '&#926;' => 'Ξ',
										'&#927;' => 'Ο', '&#928;' => 'Π', '&#929;' => 'Ρ', '&#931;' => 'Σ', '&#932;' => 'Τ', '&#933;' => 'Υ', '&#934;' => 'Φ', '&#935;' => 'Χ', '&#936;' => 'Ψ',
										'&#937;' => 'Ω', '&#945;' => 'α', '&#946;' => 'β', '&#947;' => 'γ', '&#948;' => 'δ', '&#949;' => 'ε', '&#950;' => 'ζ', '&#951;' => 'η', '&#952;' => 'θ',
										'&#953;' => 'ι', '&#954;' => 'κ', '&#955;' => 'λ', '&#956;' => 'μ', '&#957;' => 'ν', '&#958;' => 'ξ', '&#959;' => 'ο', '&#960;' => 'π', '&#961;' => 'ρ',
										'&#962;' => 'ς', '&#963;' => 'σ', '&#964;' => 'τ', '&#965;' => 'υ', '&#966;' => 'φ', '&#967;' => 'χ', '&#968;' => 'ψ', '&#969;' => 'ω', '&#977;' => 'ϑ',
										'&#978;' => 'ϒ', '&#982;' => 'ϖ', '&#338;' => 'Œ', '&#339;' => 'œ', '&#352;' => 'Š', '&#353;' => 'š', '&#376;' => 'Ÿ', '&#402;' => 'ƒ', '&#710;' => 'ˆ',
										'&#732;' => '˜', '&#8211;' => '–','&#8212;' => '—','&#8216;' => '‘','&#8217;' => '’','&#8218;' => '‚','&#8220;' => '“','&#8221;' => '”','&#8222;' => '„',
										'&#8224;' => '†','&#8225;' => '‡','&#8226;' => '•','&#8230;' => '…','&#8240;' => '‰','&#8242;' => '′','&#8243;' => '″','&#8249;' => '‹','&#8250;' => '›',
										'&#8254;' => '‾','&#8364;' => '€','&#8482;' => '™','&#8592;' => '←','&#8593;' => '↑','&#8594;' => '→','&#8595;' => '↓','&#8596;' => '↔','&#8629;' => '↵',
										'&#8968;' => '⌈','&#8969;' => '⌉','&#8970;' => '⌊','&#8971;' => '⌋','&#9674;' => '◊','&#9824;' => '♠','&#9827;' => '♣','&#9829;' => '♥','&#9830;' => '♦');

	public static $uppertolowerent =  array("Č" => "č", "Ď" => "ď", "Ě" => "ě", "Ľ" => "ľ", "Ň" => "ň", "Ř" => "ř", "Š" => "š", "Ť" => "ť", "Ž" => "ž","Ä" => "ä", "Ö" => "ö", "Ü" => "ü",
										"&Auml;" => "ä", "&#196;" => "ä", "&Ouml;" => "ö", "&#214;" => "ö", "&Uuml;" => "ü", "&#220;" => "ü","À" => "à", "È" => "è", "Ì" => "ì", "Ò" => "ò", "Ù" => "ù",
            							"É" => "é", "Í" => "í", "Ó" => "ó", "Ú" => "ú", "Ã" => "ã", "Ñ" => "ñ", "Õ" => "õ", "Ũ" => "ũ", "Â" => "â", "Ê" => "ê", "Î" => "î", "Ô" => "ô", "Û" => "û",
            							"Å" => "å", "Ů" => "ů", "Æ" => "æ", "Ç" => "ç", "Ø" => "ø", "Ë" => "ë", "Ï" => "ï", "Ğ" => "ğ", "İ" => "i", "Ş" => "ş", "Ħ" => "ħ", "Ĥ" => "ĥ", "Ĵ" => "ĵ",
            							"Ż" => "ż", "Ċ" => "ċ", "Ĉ" => "ĉ", "Ŭ" => "ŭ", "Ŝ" => "ŝ", "Ă" => "ă", "Ő" => "ő", "Ĺ" => "ĺ", "Ć" => "ć", "Ű" => "ű", "Ţ" => "ţ", "Ń" => "ń", "Đ" => "đ",
            							"Ŕ" => "ŕ", "Á" => "á", "Ś" => "ś", "Ź" => "ź", "Ł" => "ł", "˘" => "˛", "ĸ" => "˛", "Ŗ" => "ŗ", "Į" => "į", "Ę" => "ę", "Ė" => "ė", "Ð" => "ð","Ņ" => "ņ",
            							"Ō" => "ō", "Ų" => "ų", "Ý" => "ý", "Þ" => "þ", "Ą" => "ą", "Ē" => "ē", "Ģ" => "ģ", "Ī" => "ī", "Ĩ" => "ĩ", "Ķ" => "ķ", "Ļ" => "ļ", "Ŧ" => "ŧ", "Ū" => "ū",
            							"Ŋ" => "ŋ", "Ā" => "ā", "Ḃ" => "ḃ", "Ḋ" => "ḋ", "Ẁ" => "ẁ", "Ẃ" => "ẃ", "Ṡ" => "ṡ", "Ḟ" => "ḟ", "Ṁ" => "ṁ", "Ṗ" => "ṗ", "Ẅ" => "ẅ", "Ŵ" => "ŵ", "Ṫ" => "ṫ", "Ŷ" => "ŷ");

	public static $uppertolowergreek = array("Α" => "α", "Β" => "β", "Γ" => "γ", "Δ" => "δ", "Ε" => "ε", "Ζ" => "ζ", "Η" => "η", "Θ" => "θ", "Ι" => "ι", "Κ" => "κ", "Λ" => "λ", "Μ" => "μ",
                							"Ν" => "ν", "Ξ" => "ξ", "Ο" => "ο", "Π" => "π", "Ρ" => "ρ", "Σ" => "σ", "Τ" => "τ", "Υ" => "υ", "Φ" => "φ", "Χ" => "χ", "Ψψ" => "","Ω" => "ω");

	public static $upptertolowercyrillic = array(
										//      basic Cyrillian alphabet
										"А" => "а", "Б" => "б", "В" => "в", "Г" => "г", "Ґ" => "ґ", "Ѓ" => "ѓ", "Д" => "д", "Ђ" => "ђ", "Е" => "е", "Ё" => "ё", "Є" => "є", "Ж" => "ж",
										"З" => "з", "Ѕ" => "ѕ", "И" => "и", "І" => "і", "Ї" => "ї", "Й" => "й", "Ј" => "ј", "К" => "к", "Ќ" => "ќ", "Л" => "л", "Љ" => "љ", "М" => "м","Н" => "н",
										"Њ" => "њ", "О" => "о", "П" => "п", "Р" => "р", "С" => "с", "Т" => "т", "Ћ" => "ћ", "У" => "у", "Ў" => "ў", "Ф" => "ф", "Х" => "х", "Ѡ" => "ѡ",          //     ex Greek 'OMEGA'
										"Ц" => "ц", "Ч" => "ч", "Џ" => "џ", "Ш" => "ш", "Щ" => "щ", "Ъ" => "ъ", "Ы" => "ы", "Ь" => "ь", "Ы" => "ы", "Э" => "э", "Ю" => "ю", "Я" => "я",
										"Ѐ" => "ѐ", "Ђ" => "ђ", "Ї" => "ї", "Ѝ" => "ѝ",
										"Ѥ" => "ѥ",         //      extended Cyrillic
										"Ѧ" => "ѧ", "Ѫ" => "ѫ", "Ѩ" => "ѩ", "Ѭ" => "ѭ", "Ѯ" => "ѯ", "Ѱ" => "ѱ", "Ѳ" => "ѳ", "Ѵ" => "ѵ",
										"Đ" => "đ", "Ǵ" => "ǵ", "Ê" => "ê", "Ẑ" => "ẑ", "Ì" => "ì", "Ï" => "ï", "Jˇ" => "ǰ", "L̂" => "l̂", "N̂" => "n̂", "Ć" => "ć", "Ḱ" => "ḱ", "Ŭ" => "ŭ",
										"D̂" => "d̂", "Ŝ" => "ŝ", "Û" => "û", "Â" => "â", "G̀" => "g", "Ě" => "ě", "G̀" => "g", "Ġ" => "ġ", "Ğ" => "ğ", "Ž̦" => "ž", "Ķ" => "ķ", "K̄" => "k̄",
										"Ṇ" => "ṇ", "Ṅ" => "ṅ", "Ṕ" => "ṕ", "Ò" => "ò", "Ç" => "ç", "Ţ" => "ţ", "Ù" => "ù", "U" => "u", "Ḩ" => "ḩ", "C̄" => "c̄", "Ḥ" => "ḥ", "C̆" => "c̆",
										"Ç̆" => "ç̆", "Z̆" => "z̆", "Ă" => "ă", "Ä" => "ä", "Ĕ" => "ĕ", "Z̄" => "z̄", "Z̈" => "z̈", "Ź" => "ź", "Î" => "î", "Ö" => "ö", "Ô" => "ô", "Ü" => "ü",
										"Ű" => "ű", "C̈" => "c̈", "Ÿ" => "ÿ", "Ҋ" => "ҋ", "Ҍ" => "ҍ", "Ҏ" => "ҏ", "Ґ" => "ґ", "Ғ" => "ғ", "Ҕ" => "ҕ", "Җ" => "җ", "Ҙ" => "ҙ", "Қ" => "қ",
										"Ҝ" => "ҝ", "Ҟ" => "ҟ", "Ҡ" => "ҡ", "Ң" => "ң", "Ҥ" => "ҥ", "Ҧ" => "ҧ", "Ҩ" => "ҩ", "Ҫ" => "ҫ", "Ҭ" => "ҭ", "Ү" => "ү", "Ұ" => "ұ", "Ҳ" => "ҳ",
										"Ҵ" => "ҵ", "Ҷ" => "ҷ", "Ҹ" => "ҹ", "Һ" => "һ", "Ҽ" => "ҽ", "Ҿ" => "ҿ", "Ӂ" => "ӂ", "Ӄ" => "ӄ", "Ӆ" => "ӆ", "Ӈ" => "ӈ", "Ӊ" => "ӊ", "Ӌ" => "ӌ",
										"Ӎ" => "ӎ", "Ӑ" => "ӑ", "Ӓ" => "ӓ", "Ӕ" => "ӕ", "Ӗ" => "ӗ", "Ә" => "ә", "Ӛ" => "ӛ", "Ӝ" => "ӝ", "Ӟ" => "ӟ", "Ӡ" => "ӡ", "Ӣ" => "ӣ", "Ӥ" => "ӥ",
										"Ӧ" => "ӧ", "Ө" => "ө", "Ӫ" => "ӫ", "Ӭ" => "ӭ", "Ӯ" => "ӯ", "Ӱ" => "ӱ", "Ӳ" => "ӳ", "Ӵ" => "ӵ", "Ӷ" => "ӷ", "Ӹ" => "ӹ", "Ӽ" => "ӽ", "Ӿ" => "ӿ",
										"Ѡ" => "ѡ",         //      historical Cyrillic
										"Ѣ" => "ѣ", "Ѥ" => "ѥ", "Ѧ" => "ѧ", "Ѩ" => "ѩ", "Ѫ" => "ѫ", "Ѭ" => "ѭ", "Ѯ" => "ѯ", "Ѱ" => "ѱ", "Ѳ" => "ѳ", "Ѵ" => "ѵ", "Ѷ" => "ѷ", "Ѹ" => "ѹ",
										"Ѻ" => "ѻ", "Ѽ" => "ѽ", "Ѿ" => "ѿ", "Ҁ" => "ҁ", "Ǎ" => "ǎ", "F̀" => "f̀", "Ỳ" => "ỳ",  "Ð?" => "Ð°", "Ð‘" => "Ð±", "Ð’" => "Ð²", "Ð“" => "Ð³",
										"Ð”" => "Ð´", "Ð•" => "Ðµ", "Ð–" => "Ð¶", "Ð—" => "Ð·", "Ð˜" => "Ð¸", "Ð™" => "Ð¹", "Ðš" => "Ðº", "Ð›" => "Ð»", "Ðœ" => "Ð½", "Ðž" => "Ð¾",
										"ÐŸ" => "Ð¿", "Ð " => "Ñ€", "Ð¡" => "Ñ?", "Ð¢" => "Ñ‚", "Ð£" => "Ñƒ", "Ð¤" => "Ñ„", "Ð¥" => "Ñ…", "Ð¦" => "Ñ†", "Ð§" => "Ñ‡", "Ð¨" => "Ñˆ",
										"Ð©" => "Ñ‰", "Ðª" => "ÑŠ", "Ð«" => "Ñ‹", "Ð¬" => "ÑŒ", "Ð­" => "Ñ?", "Ð®" => "ÑŽ", "Ð¯" => "Ñ?",  "Ð?" => "Ñ‘", "Ð‚" => "Ñ’", "Ðƒ" => "Ñ“",
										"Ð„" => "Ñ”", "Ð…" => "Ñ•", "Ð†" => "Ñ–", "Ð‡" => "Ñ—", "Ðˆ" => "Ñ˜", "Ð‰" => "Ñ™", "ÐŠ" => "Ñš", "Ð‹" => "Ñ›", "ÐŒ" => "Ñœ", "ÐŽ" => "Ñž", "Ð?" => "ÑŸ");
}