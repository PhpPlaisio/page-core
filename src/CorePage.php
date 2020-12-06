<?php
declare(strict_types=1);

namespace Plaisio\Page;

use Plaisio\Kernel\Nub;
use Plaisio\Response\Response;

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
   *
   * @api
   * @since 1.0.0
   */
  protected int $cmpId;

  /**
   * The preferred language (lan_id) of the page requester.
   *
   * @var int
   *
   * @api
   * @since 1.0.0
   */
  protected int $lanId;

  /**
   * The profile ID (pro_id) of the page requestor.
   *
   * @var int
   *
   * @api
   * @since 1.0.0
   */
  protected int $proId;

  /**
   * The response object.
   *
   * @var Response|null
   *
   * @api
   * @since 1.0.0
   */
  protected ?Response $response;

  /**
   * The user ID (usr_id) of the page requestor.
   *
   * @var int
   *
   * @api
   * @since 1.0.0
   */
  protected int $usrId;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @api
   * @since 1.0.0
   */
  public function __construct()
  {
    $this->cmpId = Nub::$nub->company->cmpId;
    $this->proId = Nub::$nub->session->proId;
    $this->usrId = Nub::$nub->session->usrId;
    $this->lanId = Nub::$nub->babel->lanId;
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
}

//----------------------------------------------------------------------------------------------------------------------
