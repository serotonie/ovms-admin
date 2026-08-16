# define envsubst version
ARG ENVSUBST_VERSION=v1.4.3
ARG GO_IMAGE_VERSION=1.26.6-trixie

# Use a pinned Go image as a builder for a8m/envsubst.
FROM golang:${GO_IMAGE_VERSION} AS envsubst_builder

ARG ENVSUBST_VERSION

WORKDIR /src
RUN set -ex; \
  apt-get update && apt-get install -y --no-install-recommends ca-certificates && rm -rf /var/lib/apt/lists/*

ENV CGO_CFLAGS="-I/usr/local/include -fPIC"
ENV CGO_LDFLAGS="-shared -Wl,-unresolved-symbols=ignore-all"
ENV CGO_ENABLED=1

# Bring TARGETPLATFORM to the build scope
ARG TARGETPLATFORM
ARG BUILDPLATFORM

# Install TARGETPLATFORM parser to translate its value to GOOS, GOARCH, and GOARM
COPY --from=tonistiigi/xx:golang / /
RUN go env

# Install needed libc and gcc for target platform.
RUN set -ex; \
  if [ ! -z "$TARGETPLATFORM" ]; then \
  case "$TARGETPLATFORM" in \
  "linux/arm64") \
  apt-get update && apt-get install -y --no-install-recommends gcc-aarch64-linux-gnu libc6-dev-arm64-cross && rm -rf /var/lib/apt/lists/* \
  ;; \
  "linux/arm/v7") \
  apt-get update && apt-get install -y --no-install-recommends gcc-arm-linux-gnueabihf libc6-dev-armhf-cross && rm -rf /var/lib/apt/lists/* \
  ;; \
  "linux/arm/v6") \
  apt-get update && apt-get install -y --no-install-recommends gcc-arm-linux-gnueabihf libc6-dev-armel-cross libc6-dev-armhf-cross && rm -rf /var/lib/apt/lists/* \
  ;; \
  esac \
  fi

RUN set -ex; \
  mkdir -p /out && \
  GOBIN=/out go install github.com/a8m/envsubst/cmd/envsubst@${ENVSUBST_VERSION}

FROM serotonie/mosquitto-go-auth
COPY --from=envsubst_builder /out/envsubst /usr/local/bin/envsubst
RUN chmod +x /usr/local/bin/envsubst
COPY ./template ./template
COPY docker-entrypoint.sh .
RUN chmod +x /docker-entrypoint.sh

RUN set -ex; \
  mkdir -p /mosquitto/config /mosquitto/data /mosquitto/log && \
  chown -R mosquitto:mosquitto /mosquitto && \
  cd mosquitto && \
  rm -r auth conf.d && \
  cp go-auth.so go-auth_mqtt.so && \
  cp go-auth.so go-auth_mqtts.so && \
  mv go-auth.so go-auth_wss.so

USER mosquitto

ENTRYPOINT [ "/docker-entrypoint.sh", "--" ]
CMD ["/usr/sbin/mosquitto", "-c", "/mosquitto/config/mosquitto.conf"]
