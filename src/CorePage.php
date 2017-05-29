<?php
//----------------------------------------------------------------------------------------------------------------------
namespace SetBased\Abc\Page;

use SetBased\Abc\Abc;
use SetBased\Abc\Error\InvalidUrlException;
use SetBased\Abc\Helper\Html;
use SetBased\Abc\Helper\Url;

//----------------------------------------------------------------------------------------------------------------------
/**
 * Abstract parent class for all pages.
 */
abstract class CorePage implements Page
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The ID of the company of the page requester.
   *
   * @var int
   */
  protected $cmpId;

  /**
   * The keywords to be included in a meta tag for this page.
   *
   * var string[]
   */
  protected $keywords = [];

  /**
   * The preferred language (lan_id) of the page requester.
   *
   * @var int
   */
  protected $lanId;

  /**
   * The attributes of the meta elements of this page.
   *
   * @var array[]
   */
  protected $metaAttributes = [];

  /**
   * The profile ID (pro_id) of the page requestor.
   *
   * @var int
   */
  protected $proId;

  /**
   * The user ID (usr_id) of the page requestor.
   *
   * @var int
   */
  protected $usrId;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @api
   * @since 1.0.0
   */
  public function __construct()
  {
    $abc = Abc::getInstance();

    $this->cmpId = $abc->getCmpId();
    $this->proId = $abc->getProId();
    $this->usrId = $abc->getUsrId();
    $this->lanId = $abc->getLanId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of a boolean CGI variable.
   *
   * If bivalent is applied returns:
   * <ul>
   *   <li>true if the CGI variable is set and is not empty
   *   <li>false otherwise.
   * </ul>
   * If trinary logic is applied returns:
   * <ul>
   *   <li>true if the CGI variable is set and is not empty
   *   <li>false if the CGI variable is set and is empty
   *   <li>null if the CGI variable not set.
   * </ul>
   *
   * @param string $name    The name of the CGI variable.
   * @param bool   $trinary If true trinary (a.k.a  three-valued) logic will be applied. Otherwise, bivalent logic will
   *                        be applied.
   *
   * @return bool|null
   *
   * @api
   * @since 1.0.0
   */
  public static function getCgiBool($name, $trinary = false)
  {
    if (isset($_GET[$name]))
    {
      return !empty($_GET[$name]);
    }

    return ($trinary) ? null : false;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of an obfuscated database ID.
   *
   * @param string   $name    The name of the CGI variable.
   * @param string   $label   An alias for the column holding database ID and must corresponds with label that was used
   *                          to obfuscate the database ID.
   * @param int|null $default The value to be used when the CGI variable is not set.
   *
   * @return int|null
   * @api
   * @since 1.0.0
   */
  public static function getCgiId($name, $label, $default = null)
  {
    if (isset($_GET[$name]))
    {
      return Abc::deObfuscate($_GET[$name], $label);
    }

    return $default;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Return the value of a CGI variable holding an URL.
   *
   * This method will protect against unvalidated redirects, see
   * <https://www.owasp.org/index.php/Unvalidated_Redirects_and_Forwards_Cheat_Sheet>.
   *
   * @param string      $name          The name of the CGI variable.
   * @param string|null $default       The URL to be used when the CGI variable is not set.
   * @param bool        $forceRelative If set the URL must be a relative URL. If the URL is not a relative URL an
   *                                   exception will be thrown.
   *
   * @return string|null
   *
   * @throws InvalidUrlException
   *
   * @api
   * @since 1.0.0
   */
  public static function getCgiUrl($name, $default = null, $forceRelative = true)
  {
    $url = (isset($_GET[$name])) ? $_GET[$name] : $default;

    if ($forceRelative && $url!==null && !Url::isRelative($url))
    {
      throw new InvalidUrlException("Value '%s' of CGI variable '%s' is not a relative URL", $url, $name);
    }

    return $url;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of a CGI variable.
   *
   * For retrieving a CGI variable with a relative URI use {@link getCgiUrl}.
   *
   * @param string      $name    The name of the CGI variable.
   * @param string|null $default The value to be used when the CGI variable is not set.
   *
   * @return string|null
   *
   * @api
   * @since 1.0.0
   */
  public static function getCgiVar($name, $default = null)
  {
    return $_GET[$name] ?? $default;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string with holding a boolean CGI variable that can be used as a part of a URL.
   *
   * If bivalent is applied returns:
   * <ul>
   * <li>a clean CGI variable set to 1 if the value of the CGI variable is set and is not empty,
   * <li>an empty string otherwise.
   * <ul>
   * If trinary logic is applied returns:
   * <ul>
   * <li>a clean CGI variable set to 1 if the value of the CGI variable is set and is not empty,
   * <li>a clean CGI variable set to 0 if the value of the CGI variable is set and is empty,
   * <li>an empty string otherwise.
   * <ul>
   *
   * @param string $name    The name of the boolean CGI variable.
   * @param mixed  $value   The value of the CGI variable. Only and only a nonempty value evaluates to true.
   * @param bool   $trinary If true trinary (a.k.a  three-valued) logic will be applied. Otherwise, bivalent logic will
   *                        be applied.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiBool($name, $value, $trinary = false)
  {
    if (!empty($value))
    {
      return '/'.$name.'/1';
    }

    if ($trinary && $value!==null)
    {
      return '/'.$name.'/0';
    }

    return '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string with holding a CGI variable that can be used as a part of a URL.
   *
   * @param string      $name  The name of the CGI variable.
   * @param mixed       $value The value (must be a scalar) of the CGI variable.
   * @param string|null $label The alias for the column holding database ID.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiId($name, $value, $label)
  {
    if ($value!==null)
    {
      return '/'.$name.'/'.Abc::obfuscate($value, $label);
    }

    return '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns (virtual) filename based on the slug of a string that can be safely used in an URL.
   *
   * @param string $string    The string.
   * @param string $extension The extension of the (virtual) filename.
   *
   * @return string
   */
  public static function putCgiSlugName($string, $extension = '.html')
  {
    $slug = Html::txt2Slug($string);

    return ($slug==='') ? '' : '/'.$slug.$extension;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string with holding a CGI variable with an URL as value that can be used as a part of a URL.
   *
   * Note: This method is an alias of {@link putCgiVar}.
   *
   * @param string      $name  The name of the CGI variable.
   * @param string|null $value The value of the CGI variable.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiUrl($name, $value)
  {
    return self::putCgiVar($name, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string with holding a CGI variable that can be used as a part of a URL.
   *
   * @param string $name  The name of the CGI variable.
   * @param mixed  $value The value (must be a scalar) of the CGI variable.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiVar($name, $value)
  {
    return ($value!==null) ? '/'.$name.'/'.urlencode($value) : '';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * If a page needs additional and page specific authorization and/or security checks this method must be overridden.
   *
   * When a HTTP request must be denied a NotAuthorizedException must be raised.
   *
   * @api
   * @since 1.0.0
   */
  public function checkAuthorization()
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Must be implemented in child classes to echo the actual page content, i.e. the inner HTML of the body tag.
   *
   * @return void
   *
   * @api
   * @since 1.0.0
   */
  abstract public function echoPage();

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * If this page can be requested via multiple URI's and one URI is preferred this method must be overridden to return
   * the preferred URI of this page.
   *
   * Typically this method will be used when the URL contains some ID and an additional title.
   * Example:
   * Initially a page with an article about a book is created with title "Harry Potter and the Sorcerer's Stone" and the
   * URI is /book/123456/Harry_Potter_and_the_Sorcerer's_Stone.html. After this article has been edited the URI is
   * /book/123456/Harry_Potter_and_the_Philosopher's_Stone.html. The later URI is the preferred URI now.
   *
   * If the preferred URI is set and different from the requested URI the user agent will be redirected to the
   * preferred URI with HTTP status code 301 (Moved Permanently).
   *
   * @return string|null The preferred URI of this page.
   *
   * @api
   * @since 1.0.0
   */
  public function getPreferredUri()
  {
    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds a meta element to this page.
   *
   * {@deprecated}
   *
   * @param array $attributes The attributes of the meta element.
   */
  public function metaAddElement($attributes)
  {
    $this->metaAttributes[] = $attributes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds a keyword to the keywords to be included in the keyword meta element of this page.
   *
   * {@deprecated}
   *
   * @param string $keyword The keyword.
   */
  public function metaAddKeyword($keyword)
  {
    $this->keywords[] = $keyword;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds keywords to the keywords to be included in the keyword meta element of this page.
   *
   * {@deprecated}
   *
   * @param string[] $keywords The keywords.
   */
  public function metaAddKeywords($keywords)
  {
    $this->keywords = array_merge($this->keywords, $keywords);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@deprecated}
   *
   * @param string $pageTitleAddendum
   */
  protected function appendPageTitle($pageTitleAddendum)
  {
    Abc::getInstance()->appendPageTitle($pageTitleAddendum);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the meta tags within the HTML document.
   *
   * {@deprecated}
   */
  protected function echoMetaTags()
  {
    if (!empty($this->keywords))
    {
      $this->metaAttributes[] = ['name' => 'keywords', 'content' => implode(',', $this->keywords)];
    }

    $this->metaAttributes[] = ['charset' => Html::$encoding];

    foreach ($this->metaAttributes as $metaAttribute)
    {
      echo Html::generateVoidElement('meta', $metaAttribute);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document leader, i.e. the start html tag, the head element, and start body tag.
   */
  protected function echoPageLeader()
  {
    $lan_code = Abc::getInstance()->getLanCode();
    echo '<!DOCTYPE html>';
    echo '<html xmlns="http://www.w3.org/1999/xhtml"', Html::generateAttribute('xml:lang', $lan_code),
    Html::generateAttribute('lang', $lan_code), '>';
    echo '<head>';

    // Echo the meta tags.
    $this->echoMetaTags();

    // Echo the title of the XHTML document.
    echo '<title>', Html::txt2Html(Abc::getInstance()->getPageTitle()), '</title>';

    // Echo style sheets (if any).
    Abc::$assets->echoCascadingStyleSheets();

    echo '</head><body>';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document trailer, i.e. the end body and html tags, including the JavaScript code that will be
   * executed using RequireJS.
   */
  protected function echoPageTrailer()
  {
    Abc::$assets->echoJavaScript();

    echo '</body></html>';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@deprecated}
   *
   * @return int
   */
  protected function getPagIdOrg()
  {
    return Abc::getInstance()->getPagIdOrg();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@deprecated}
   *
   * @return string
   */
  protected function getPageTitle()
  {
    return Abc::getInstance()->getPageTitle();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@deprecated}
   *
   * @return string
   */
  protected function getPtbId()
  {
    return Abc::getInstance()->getPtbId();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * {@deprecated}
   *
   * @param string $pageTitle The new title of the page.
   */
  protected function setPageTitle($pageTitle)
  {
    Abc::getInstance()->setPageTitle($pageTitle);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
