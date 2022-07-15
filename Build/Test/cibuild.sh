#!/usr/bin/env bash

echo "PWD: $(pwd)"

TEST_SUITES_STATUS_CODE=0

echo "Check compliance against TYPO3 Coding Standards: See https://github.com/TYPO3/coding-standards"
if ! composer t3:standards:fix -- --diff --verbose --dry-run && rm .php-cs-fixer.cache
then
  echo "Some files are not compliant to TYPO3 Coding Standards"
  echo "Please fix the files listed above."
  echo "Tip for auto fix: execute following command: "
  echo "  composer tests:setup && composer t3:standards:fix"
  TEST_SUITES_STATUS_CODE=1
else
  echo "The code is TYPO3 Coding Standards compliant! Great job!"
fi
echo -e "\n\n"


echo "Run unit tests"
if ! composer tests:unit
then
   echo "Unit tests are failing please fix them"
   TEST_SUITES_STATUS_CODE=1
fi

exit $TEST_SUITES_STATUS_CODE
