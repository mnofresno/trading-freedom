FROM petruisfan/docker-ionic:1.0

COPY ./build.sh /bin/build.sh
RUN yes | $ANDROID_HOME/tools/bin/sdkmanager --licenses || true
RUN npm -g install bower gulp gulp-cli

