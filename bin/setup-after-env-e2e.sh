echo "[After] Setting up environment for e2e tests"

echo "* The working directory is: $(pwd)"

echo "[1] Activate Visualizer tracking"
npm run wp-env run tests-cli wp option set visualizer_logger_flag "yes"

echo "[2] Install plugins"
npm run wp-env run tests-cli wp plugin install visualizer
npm run wp-env run tests-cli wp plugin activate visualizer

echo "[3] Activate SDK as plugin"
npm run wp-env run tests-cli wp plugin activate themeisle-sdk-main