FROM python:3-slim AS prod

ARG USERNAME=worker
ARG USER_UID=1000
ARG USER_GID=$USER_UID
ENV WORKDIR=/opt/src
ENV VIRTUAL_ENV=${WORKDIR}/venv

ENV DB_CONFIG_PATH=database/config.py

RUN eux;    apt-get update &&\
            apt-get upgrade -y --no-install-recommends
            
RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME -s /usr/bin/bash

WORKDIR ${WORKDIR}

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
                git

USER ${USERNAME}
