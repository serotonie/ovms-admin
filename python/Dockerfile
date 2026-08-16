ARG PYTHON_IMAGE_VERSION=3.12-slim

FROM python:${PYTHON_IMAGE_VERSION} AS base_prod

ARG USERNAME=worker
ARG USER_UID=1000
ARG USER_GID=$USER_UID
ENV WORKDIR=/app/src
ENV VIRTUAL_ENV=${WORKDIR}/venv

RUN set -eux;    apt-get update &&\
    apt-get install -y --no-install-recommends \
    gcc \
    python3-dev && \
    rm -rf /var/lib/apt/lists/*

RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME -s /usr/bin/bash

RUN set -eux;   mkdir /app; \
    chown -R $USERNAME:$USERNAME ./app

RUN ln -sf /bin/bash /bin/sh

USER ${USERNAME}

RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

WORKDIR ${WORKDIR}

RUN pip install --upgrade pip

COPY --chown=$USERNAME:$USERNAME ./docker-entrypoint.sh /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD [ "python", "-u", "main.py" ]


FROM base_prod AS trips_recorder

RUN pip install --upgrade pip
COPY --chown=$USERNAME:$USERNAME ./trips-recorder/requirements.txt ./
RUN pip install -r ./requirements.txt \
    && rm requirements.txt

COPY --chown=$USERNAME:$USERNAME ./trips-recorder/src ./

USER ${USERNAME}


FROM trips_recorder AS trips_recorder_dev

USER root

RUN set -eux;   apt-get update && apt-get install -y --no-install-recommends \
    git \
    gcc \
    python3-dev && \
    rm -rf /var/lib/apt/lists/*

USER ${USERNAME}
