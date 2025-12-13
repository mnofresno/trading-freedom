FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive

# Install basics
RUN apt-get update && apt-get install -y \
    openjdk-8-jdk \
    curl \
    git \
    unzip \
    wget \
    gradle \
    python2 \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 12 (Compatible with Gulp 3/Ionic 1 era)
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - && \
    apt-get install -y nodejs

# Fix for python2 requirement in older node-gyp
RUN npm config set python python2

# Install Ionic and Cordova
# Pinning versions known to work with Ionic 1
RUN npm install -g cordova@9.0.0 ionic@1.7.16 gulp@3.9.1 bower

# Android SDK
ENV ANDROID_SDK_ROOT /opt/android-sdk
RUN mkdir -p ${ANDROID_SDK_ROOT}/cmdline-tools && \
    wget -q https://dl.google.com/android/repository/commandlinetools-linux-6609375_latest.zip -O android-sdk.zip && \
    unzip -q android-sdk.zip -d ${ANDROID_SDK_ROOT}/cmdline-tools && \
    mv ${ANDROID_SDK_ROOT}/cmdline-tools/tools ${ANDROID_SDK_ROOT}/cmdline-tools/latest && \
    rm android-sdk.zip

ENV PATH ${PATH}:${ANDROID_SDK_ROOT}/cmdline-tools/latest/bin:${ANDROID_SDK_ROOT}/platform-tools

# Accept licenses and install platforms
# Installing android-28 (Android 9) as a safe baseline for legacy apps
RUN yes | sdkmanager --licenses && \
    sdkmanager "platform-tools" "platforms;android-28" "build-tools;28.0.3"

WORKDIR /app

# Entry point to keep container running if needed, or straight to build
CMD ["bash"]
