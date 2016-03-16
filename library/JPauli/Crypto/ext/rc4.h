/*
 * rc4.h
 *
 *  Created on: 24 mars 2016
 *      Author: julien.pauli
 */

#ifndef LIBRARY_JPAULI_CRYPTO_RC4_EXT_RC4_H_
#define LIBRARY_JPAULI_CRYPTO_RC4_EXT_RC4_H_

#include "php.h"

#define SHA1_SIZE      40
#define SHA1_RAW_SIZE  20
#define RC4_DROP_BYTES 1024

typedef struct _rc4 {
	unsigned char S[256];
	void (*roll)(void *);
	unsigned short i;
	unsigned short j;
	unsigned short k;
	unsigned short z;
} rc4;

typedef struct _rc4_obj {
	rc4 rc4;
	zend_object zobj;
} rc4_obj;

#define FETCH_RC4_OBJ_ZOBJ(zobject) rc4_obj *obj = (rc4_obj *)((char *)zobject - XtOffsetOf(rc4_obj, zobj))
#define FETCH_RC4_OBJ(zval)         FETCH_RC4_OBJ_ZOBJ(Z_OBJ_P(zval))
#define FETCH_RC4_OBJ_THIS          FETCH_RC4_OBJ(getThis())

#define S(i) obj->rc4.S[i]
#define I obj->rc4.i
#define J obj->rc4.j
#define K obj->rc4.k
#define Z obj->rc4.z

#define SWAP(obj) S(I) = S(I) ^ S(J); S(J) = S(I) ^ S(J); S(I) = S(I) ^ S(J);
#define ROLL(obj) obj->rc4.roll(obj);

#define RC4_roll(obj) do { \
	I = (I + 1) & 0xFF; \
	J = (J + S(I)) & 0xFF; \
	SWAP(obj) \
} while(0);

#define Spritz_roll(obj) do { \
	I = (I + 0xFF) & 0xFF; \
	J = (K + S(J + S(I))) & 0xFF; \
	K = (K + I + S(J)) & 0xFF; \
	SWAP(obj) \
} while (0);

static zend_object *rc4_create_object(zend_class_entry *ce);
static PHP_METHOD(RC4, __construct);
static PHP_METHOD(RC4, reset);
static PHP_METHOD(RC4, output);
static PHP_METHOD(RC4, getState);

#endif /* LIBRARY_JPAULI_CRYPTO_RC4_EXT_RC4_H_ */
