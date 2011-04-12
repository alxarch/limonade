<?php


// MAIN PUBLIC FUNCTIONS _______________________________________________________


/*
 * Event callback to be triggered on configure_before
 *
 * Sets defaults to be overwritten by the user.
 * @access private
 *
 */
function _i18n_configure_before()
{
  option('i18n.languages', array('en'));
  option('i18n.gettext.domain', 'messages');
  option('i18n.gettext.encoding', 'UTF-8');
  option('i18n.gettext.path', file_path(option('data_dir'), 'i18n'));
}

/*
 * Event callback to be triggered on configure_before
 *
 * Loads php-gettext
 * @access private
 *
 */
function _i18n_session_after()
{
  if(!isset($_SESSION[SID]['lang']))
  {
    $lang = i18n_guess();
    i18n_lang($lang);
  }
  
  i18n_load_gettext();
}

/**
 * Setups internationalization via php-gettext.
 *
 */
function i18n_load_gettext()
{
  require_once(file_path(option('lib_dir'),'vendor', 'php-gettext','gettext.inc'));
  $locale = i18n_lang();
  $domain = option('i18n.gettext.domain');
  T_setlocale(LC_MESSAGES, $locale);
  // Set the text domain as 'messages'
  T_bindtextdomain($domain, option('i18n.gettext.path'));
  T_bind_textdomain_codeset($domain, option('i18n.gettext.encoding'));
  T_textdomain($domain);
}

/**
 * Gets or sets supported languages.
 *
 * @example get preferred languages: <code>i18n_languages(lang1, lang2, ...)</code>
 * @example set preferred languages: <code>i18n_languages()</code>
 *
 */
function i18n_languages()
{
  if(func_num_args())
  {
    $langs = array();
    foreach(func_get_args() as $arg){
      $langs[] = (string) $arg;
    };
    
    option('i18n.languages', array_unique(array_filter($langs)));
  }
  
  return option('i18n.languages');
}

function i18n_user_languages()
{
  static $languages = null;
  if(null === $languages)
  {
    $languages = array('en');
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match_all(
      '/([a-zA-Z]{2})[a-z\-]*;/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches))
    {
      $languages = $matches[1];
    }
  }
  
  return $languages;
}

/**
 * Guesses user locale.
 *
 * @return string the best match to user locale settings.
 */
function i18n_guess($fallback = null)
{
  $supported = i18n_languages();
  $desired = i18n_user_languages();
  $common = array_intersect($supported, $desired);
  
  if(count($common))
  {
    return array_shift($common);
  }
  return null === $fallback ? array_shift($supported) : $fallback;
}

/**
 *
 * Only allows supported langs.
 *
 * @param string $lang
 */
function i18n_lang($lang = null)
{
  if(null !== $lang){
    $langs = i18n_languages();
    if(in_array($lang, $langs)) $_SESSION[SID]['lang'] = $lang;
  }
  return $_SESSION[SID]['lang'];
}


// HELPERS _____________________________________________________________________

/**
 * _ngettext wrapper. @see ngettext()
 */
function __n($singular, $plural, $nuber)
{
  return _ngettext($singular, $plural, $number);
}


// INIT ________________________________________________________________________

function i18n_init()
{
  // check to make sure languages are set uptill configure
  event('configure.before', '_i18n_configure_before');
  
  // wait for session initialisation to init php-gettext.
  event('session.after', '_i18n_session_after');
}
