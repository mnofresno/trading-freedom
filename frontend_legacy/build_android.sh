#!/bin/bash

# Build the docker image
echo "Building Docker image for Android build environment..."
docker build -t ionic1-android-builder .

# Run the build
echo "Running Android build in Docker..."
docker run --rm -v $(pwd):/app ionic1-android-builder /bin/bash -c "
    echo 'Installing npm dependencies...'
    npm install --unsafe-perm
    
    echo 'Installing bower dependencies...'
    bower install --allow-root
    
    echo 'Adding Android platform...'
    # Check if platform already exists to avoid errors
    if [ ! -d 'platforms/android' ]; then
        ionic cordova platform add android
    fi
    
    echo 'Building APK...'
    ionic cordova build android --debug
"
