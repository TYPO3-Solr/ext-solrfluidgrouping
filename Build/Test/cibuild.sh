#!/usr/bin/env bash

echo "PWD: $(pwd)"

export TYPO3_PATH_WEB="$(pwd)/.Build/Web"
export TYPO3_PATH_PACKAGES="$(pwd)/.Build/vendor/"

TEST_SUITES_STATUS_CODE=0

if [ "$TRAVIS" ]; then
    # Travis does not have composer's bin dir in $PATH
    export PATH="$PATH:$HOME/.composer/vendor/bin"
fi

# use from vendor dir
php-cs-fixer --version > /dev/null 2>&1
if [ $? -eq "0" ]; then
    echo "Check PSR-2 compliance"
    
    php-cs-fixer fix --diff --verbose --dry-run --rules='{"function_declaration": {"closure_function_spacing": "none"}}' Classes
    if [ $? -ne "0" ]; then
        echo "Some files are not PSR-2 compliant"
        echo "Please fix the files listed above"
        TEST_SUITES_STATUS_CODE=1
    fi
fi


echo "Run unit tests"
UNIT_BOOTSTRAP=".Build/vendor/nimut/testing-framework/res/Configuration/UnitTestsBootstrap.php"
if ! .Build/bin/phpunit \
  --configuration Build/Test/UnitTests.xml \
  --bootstrap=$UNIT_BOOTSTRAP \
  --coverage-clover=coverage.unit.clover  \
  --colors
then
   echo "Unit tests are failing please fix them"
   TEST_SUITES_STATUS_CODE=1
fi

echo "Run integration tests"

#
# Map the travis and shell variale names to the expected
# casing of the TYPO3 core.
#
if [ -n "$TYPO3_DATABASE_NAME" ]; then
	export typo3DatabaseName=$TYPO3_DATABASE_NAME
else
	echo "No environment variable TYPO3_DATABASE_NAME set. Please set it to run the integration tests."
	exit 1
fi

if [ -n "$TYPO3_DATABASE_HOST" ]; then
	export typo3DatabaseHost=$TYPO3_DATABASE_HOST
else
	echo "No environment variable TYPO3_DATABASE_HOST set. Please set it to run the integration tests."
	exit 1
fi

if [ -n "$TYPO3_DATABASE_USERNAME" ]; then
	export typo3DatabaseUsername=$TYPO3_DATABASE_USERNAME
else
	echo "No environment variable TYPO3_DATABASE_USERNAME set. Please set it to run the integration tests."
	exit 1
fi

if [[ -v TYPO3_DATABASE_PASSWORD ]]; then # because empty password is possible
	export typo3DatabasePassword=$TYPO3_DATABASE_PASSWORD
else
	echo "No environment variable TYPO3_DATABASE_PASSWORD set. Please set it to run the integration tests."
	exit 1
fi

INTEGRATION_BOOTSTRAP=".Build/vendor/nimut/testing-framework/res/Configuration/FunctionalTestsBootstrap.php"
if ! .Build/bin/phpunit \
  --configuration Build/Test/IntegrationTests.xml \
  --bootstrap=$INTEGRATION_BOOTSTRAP \
  --coverage-clover=coverage.integration.clover \
  --colors
then
    echo "Error during running the integration tests please check and fix them"
    TEST_SUITES_STATUS_CODE=1
fi

exit $TEST_SUITES_STATUS_CODE