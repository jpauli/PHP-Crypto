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
  | Author: Julien Pauli <jpauli@php.net>                                |
  +----------------------------------------------------------------------+
*/

/* $Id$ */

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_jpauli_crypto.h"
#include "ext/standard/sha1.h"
#include "rc4.h"

static zend_class_entry *rc4_ce, *spritz_ce;
static zend_object_handlers rc4_obj_handlers, spritz_obj_handlers;

ZEND_BEGIN_ARG_INFO_EX(arginfo_rc4___construct, NULL, 0, 2)
	ZEND_ARG_INFO(0, iv)
	ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()

static zend_function_entry rc4_functions[] =
{
	PHP_ME(RC4, __construct, arginfo_rc4___construct, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
	PHP_ME(RC4, reset, arginfo_rc4___construct, ZEND_ACC_PUBLIC)
	PHP_ME(RC4, output, NULL, ZEND_ACC_PUBLIC)
	PHP_ME(RC4, getState, NULL, ZEND_ACC_PUBLIC)
	PHP_FE_END
};

static zend_function_entry spritz_functions[sizeof(rc4_functions)/sizeof(rc4_functions[0])];

static void rc4_roll(void *v)
{
	rc4_obj *obj = (rc4_obj *)v;
	RC4_roll(obj)
}

static void spritz_roll(void *v)
{
	rc4_obj *obj = (rc4_obj *)v;
	Spritz_roll(obj)
}

static zend_object *rc4_create_object(zend_class_entry *ce)
{
	rc4_obj *obj  = ecalloc(1, sizeof(*obj) + zend_object_properties_size(ce));
	obj->rc4.roll = rc4_roll;
	zend_object_std_init(&obj->zobj, ce);
	object_properties_init(&obj->zobj, ce);

	obj->zobj.handlers = &rc4_obj_handlers;

	return &obj->zobj;
}

static zend_object *spritz_create_object(zend_class_entry *ce)
{
	zend_object *zobj = rc4_create_object(ce);
	FETCH_RC4_OBJ_ZOBJ(zobj);

	obj->rc4.roll = spritz_roll;

	return zobj;
}

static PHP_METHOD(RC4, __construct)
{
	char *iv, *key, *rc4_key;
	size_t iv_len, key_len;
	int i, j;
	unsigned char sha1[SHA1_RAW_SIZE] = {0};
	FETCH_RC4_OBJ_THIS;
	PHP_SHA1_CTX context;

	if (zend_parse_parameters(ZEND_NUM_ARGS(), "ss", &iv, &iv_len, &key, &key_len) == FAILURE) {
		return;
	}

	rc4_key = ecalloc(1, iv_len + key_len + 1);
	memcpy(rc4_key, key, key_len);
	memcpy(rc4_key + key_len, iv, iv_len);

	for (i=0; i<256; i++) {
		S(i) = i;
	}

	PHP_SHA1Init(&context);
	PHP_SHA1Update(&context, (unsigned char *) rc4_key, iv_len + key_len);
	PHP_SHA1Final(sha1, &context);
	efree(rc4_key);

	for (i=0, j=0; i<256; i++) {
		j = (j + sha1[i % SHA1_RAW_SIZE] + S(i)) & 0xFF;
		SWAP(obj)
	}

	for(i=0; i<RC4_DROP_BYTES; i++) {
		ROLL(obj)
	}
}

static PHP_METHOD(RC4, getState)
{
	FETCH_RC4_OBJ_THIS;

	if (zend_parse_parameters_none() == FAILURE) {
		return;
	}

	RETURN_STRINGL((char *)obj->rc4.S, 256);
}

static PHP_METHOD(RC4, reset)
{
	ZEND_MN(RC4___construct)(INTERNAL_FUNCTION_PARAM_PASSTHRU);

	RETURN_ZVAL(getThis(), 0, 0)
}

static PHP_METHOD(RC4, output)
{
	FETCH_RC4_OBJ_THIS;

	ROLL(obj)

	RETURN_STRINGL((char *)&S((S(I) + S(J)) & 0xFF), 1);
}

static PHP_METHOD(Spritz, output)
{
	FETCH_RC4_OBJ_THIS;

	ROLL(obj)

	Z = S(J + (S(I + S(S(Z) + S(K))))) & 0xFF;

	RETURN_STRINGL((char *)&Z, 1);
}

static PHP_MINIT_FUNCTION(jpauli_crypto)
{
	zend_class_entry rc4, spritz;

	memcpy(spritz_functions, rc4_functions, sizeof(rc4_functions));
	spritz_functions[2].handler = ZEND_MN(Spritz_output);

	INIT_NS_CLASS_ENTRY(rc4, "JPauli\\Crypto", "RC4", rc4_functions);
	INIT_NS_CLASS_ENTRY(spritz, "JPauli\\Crypto", "Spritz", spritz_functions);

	rc4_ce    = zend_register_internal_class(&rc4);
	spritz_ce = zend_register_internal_class_ex(&spritz, rc4_ce);

	zend_declare_class_constant_long(rc4_ce, "SHA1_SIZE", strlen("SHA1_SIZE"), SHA1_SIZE);
	zend_declare_class_constant_long(rc4_ce, "RC4_DROP_BYTES", strlen("RC4_DROP_BYTES"), RC4_DROP_BYTES);

	memcpy(&rc4_obj_handlers, zend_get_std_object_handlers(), sizeof(rc4_obj_handlers));

	rc4_ce->create_object      = rc4_create_object;
	rc4_obj_handlers.clone_obj = NULL;
	rc4_obj_handlers.offset    = XtOffsetOf(rc4_obj, zobj);

	memcpy(&spritz_obj_handlers, &rc4_obj_handlers, sizeof(rc4_obj_handlers));
	spritz_ce->create_object = spritz_create_object;

	return SUCCESS;
}

static PHP_MINFO_FUNCTION(jpauli_crypto)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "jpauli_crypto support", "enabled");
	php_info_print_table_end();
}

zend_module_entry jpauli_crypto_module_entry = {
	STANDARD_MODULE_HEADER,
	"jpauli_crypto",
	NULL,
	PHP_MINIT(jpauli_crypto),
	NULL,
	NULL,
	NULL,
	PHP_MINFO(jpauli_crypto),
	PHP_JPAULI_CRYPTO_VERSION,
	STANDARD_MODULE_PROPERTIES
};

#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif

ZEND_GET_MODULE(jpauli_crypto)
