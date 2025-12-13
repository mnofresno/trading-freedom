#!/bin/bash
# Start script to bypass NPM authentication issues by forcing public registry
echo "Starting Expo with public NPM registry..."
export npm_config_registry=https://registry.npmjs.org/
npx expo start
