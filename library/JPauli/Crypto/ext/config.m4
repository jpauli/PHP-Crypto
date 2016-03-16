dnl $Id$
dnl config.m4 for extension jpauli_crypto

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(jpauli_crypto, for jpauli_crypto support,
dnl Make sure that the comment is aligned:
dnl [  --with-jpauli_crypto             Include jpauli_crypto support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(jpauli_crypto, whether to enable jpauli_crypto support,
dnl Make sure that the comment is aligned:
[  --enable-jpauli_crypto           Enable jpauli_crypto support])

if test "$PHP_JPAULI_CRYPTO" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-jpauli_crypto -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/jpauli_crypto.h"  # you most likely want to change this
  dnl if test -r $PHP_JPAULI_CRYPTO/$SEARCH_FOR; then # path given as parameter
  dnl   JPAULI_CRYPTO_DIR=$PHP_JPAULI_CRYPTO
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for jpauli_crypto files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       JPAULI_CRYPTO_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$JPAULI_CRYPTO_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the jpauli_crypto distribution])
  dnl fi

  dnl # --with-jpauli_crypto -> add include path
  dnl PHP_ADD_INCLUDE($JPAULI_CRYPTO_DIR/include)

  dnl # --with-jpauli_crypto -> check for lib and symbol presence
  dnl LIBNAME=jpauli_crypto # you may want to change this
  dnl LIBSYMBOL=jpauli_crypto # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $JPAULI_CRYPTO_DIR/$PHP_LIBDIR, JPAULI_CRYPTO_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_JPAULI_CRYPTOLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong jpauli_crypto lib version or lib not found])
  dnl ],[
  dnl   -L$JPAULI_CRYPTO_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(JPAULI_CRYPTO_SHARED_LIBADD)

  PHP_NEW_EXTENSION(jpauli_crypto, jpauli_crypto.c, $ext_shared,, -DZEND_ENABLE_STATIC_TSRMLS_CACHE=1)
fi
