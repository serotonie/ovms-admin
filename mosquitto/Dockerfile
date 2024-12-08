# define envsubst version
ARG ENVSUBST_VERSION=v1.4.2

# Use golang:latest as a builder for a8m/envsubst
FROM golang:latest AS envsubst_builder

ARG ENVSUBST_VERSION

WORKDIR /
RUN set -ex; \
  git clone https://github.com/a8m/envsubst.git --branch ${ENVSUBST_VERSION} envsubst
WORKDIR /envsubst/cmd/envsubst

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
  apt update && apt install -y gcc-aarch64-linux-gnu libc6-dev-arm64-cross \
  ;; \
  "linux/arm/v7") \
  apt update && apt install -y gcc-arm-linux-gnueabihf libc6-dev-armhf-cross \
  ;; \
  "linux/arm/v6") \
  apt update && apt install -y gcc-arm-linux-gnueabihf libc6-dev-armel-cross libc6-dev-armhf-cross \
  ;; \
  esac \
  fi

RUN set -ex; \
  go build -o ../../envsubst

FROM serotonie/mosquitto-go-auth
COPY --from=envsubst_builder /envsubst/envsubst /usr/local/bin
RUN chmod +x /usr/local/bin/envsubst
COPY ./template ./template
COPY docker-entrypoint.sh .

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
