FROM python:3-slim AS psycopg2_builder

RUN set -eux;    apt-get update &&\
    apt-get upgrade -y --no-install-recommends

RUN set -eux;   apt-get install -y --no-install-recommends \
    libpq-dev \
    gcc \
    python3-dev

RUN python -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

RUN pip install psycopg2-binary


FROM python:3-slim AS prod

ARG USERNAME=worker
ARG USER_UID=1000
ARG USER_GID=$USER_UID
ENV WORKDIR=/app/src
ENV VIRTUAL_ENV=${WORKDIR}/venv

ENV DB_CONFIG_PATH=database/config.py

RUN set -eux;    apt-get update &&\
    apt-get upgrade -y --no-install-recommends

RUN set -eux;   apt-get install -y --no-install-recommends \
    libpq-dev

RUN mkdir /app

RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME -s /usr/bin/bash

WORKDIR ${WORKDIR}

COPY --from=psycopg2_builder --chown=$USERNAME:$USERNAME /opt/venv/lib ./venv/lib
COPY --from=psycopg2_builder --chown=$USERNAME:$USERNAME /opt/venv/lib64 ./venv/lib64

COPY --chown=$USERNAME:$USERNAME requirements.txt ./
RUN pip install -r requirements.txt
RUN rm requirements.txt

COPY --chown=$USERNAME:$USERNAME ./src ./

RUN ln -sf /bin/bash /bin/sh

USER ${USERNAME}

ENTRYPOINT [ "python", "-u", "main.py" ]

FROM prod AS dev

USER root

RUN set -eux;   apt-get install -y --no-install-recommends \
    git \
    gcc \
    python3-dev

USER ${USERNAME}
