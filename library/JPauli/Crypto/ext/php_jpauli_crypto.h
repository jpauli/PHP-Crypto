/*
  +----------------------------------------------------------------------+
  | PHP Version 7                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2016 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:  Julien Pauli <jpauli@php.net>                               |
  +----------------------------------------------------------------------+
*/

/* $Id$ */

#ifndef PHP_JPAULI_CRYPTO_H
#define PHP_JPAULI_CRYPTO_H

extern zend_module_entry jpauli_crypto_module_entry;
#define phpext_jpauli_crypto_ptr &jpauli_crypto_module_entry

#define PHP_JPAULI_CRYPTO_VERSION "0.1.0"

#ifdef PHP_WIN32
#	define PHP_JPAULI_CRYPTO_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#	define PHP_JPAULI_CRYPTO_API __attribute__ ((visibility("default")))
#else
#	define PHP_JPAULI_CRYPTO_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

#define JPAULI_CRYPTO_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(jpauli_crypto, v)

#if defined(ZTS) && defined(COMPILE_DL_JPAULI_CRYPTO)
ZEND_TSRMLS_CACHE_EXTERN()
#endif

ZEND_DLEXPORT zend_module_entry *get_module(void);

#endif
