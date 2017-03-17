echo "[Before] Setting up environment for e2e tests"

echo "* The working directory is: $(pwd)"

echo "[1] Transform into a plugin"
if [ ! -f "./themeisle-sdk.php" ]; then
  cp ./bin/themeisle-sdk.php ./
else
  echo "[Skip] The plugin file already exists"
fi