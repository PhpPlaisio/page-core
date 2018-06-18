<?php

namespace SetBased\Abc\Page;

use SetBased\Abc\Abc;
use SetBased\Abc\Exception\InvalidUrlException;
use SetBased\Abc\Helper\Html;
use SetBased\Abc\Helper\Url;

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
   * The preferred language (lan_id) of the page requester.
   *
   * @var int
   */
  protected $lanId;

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
    $this->cmpId = Abc::$companyResolver->getCmpId();
    $this->proId = Abc::$session->getProId();
    $this->usrId = Abc::$session->getUsrId();
    $this->lanId = Abc::$babel->getLanId();
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
  public static function getCgiBool(string $name, bool $trinary = false): ?bool
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
  public static function getCgiId(string $name, string $label, $default = null): ?int
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
  public static function getCgiUrl(string $name, ?string $default = null, bool $forceRelative = true): ?string
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
  public static function getCgiVar(string $name, ?string $default = null): ?string
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
   * @param ?bool  $value   The value of the CGI variable.
   * @param bool   $trinary If true trinary (a.k.a  three-valued) logic will be applied. Otherwise, bivalent logic will
   *                        be applied.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiBool(string $name, ?bool $value, bool $trinary = false): string
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
   * @param int|null    $value The value of the CGI variable.
   * @param string|null $label The alias for the column holding database ID.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiId(string $name, ?int $value, string $label): string
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
  public static function putCgiSlugName(string $string, string $extension = '.html'): string
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
  public static function putCgiUrl(string $name, ?string $value): string
  {
    return self::putCgiVar($name, $value);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns a string with holding a CGI variable that can be used as a part of a URL.
   *
   * @param string      $name  The name of the CGI variable.
   * @param string|null $value The value of the CGI variable.
   *
   * @return string
   *
   * @api
   * @since 1.0.0
   */
  public static function putCgiVar(string $name, ?string $value): string
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
  public function checkAuthorization(): void
  {
    // Nothing to do.
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * By default the response to an XMLHttpRequest equals to a normal HTTP request.
   */
  public function echoXhrResponse(): void
  {
    $this->echoPage();
  }

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
  public function getPreferredUri(): ?string
  {
    return null;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document leader, i.e. the start html tag, the head element, and start body tag.
   */
  protected function echoPageLeader(): void
  {
    echo '<!DOCTYPE html>';
    echo Html::generateTag('html',
                           ['xmlns'    => 'http://www.w3.org/1999/xhtml',
                            'xml:lang' => Abc::$babel->getCode(),
                            'lang'     => Abc::$babel->getCode()]);
    echo '<head>';

    // Echo the meta tags.
    Abc::$assets->echoMetaTags();

    // Echo the title of the XHTML document.
    Abc::$assets->echoPageTitle();

    // Echo style sheets (if any).
    Abc::$assets->echoCascadingStyleSheets();

    echo '</head><body>';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Echos the XHTML document trailer, i.e. the end body and html tags, including the JavaScript code that will be
   * executed using RequireJS.
   */
  protected function echoPageTrailer(): void
  {
    Abc::$assets->echoJavaScript();

    echo '</body></html>';
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
