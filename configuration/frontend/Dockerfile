ARG NODE_VERSION=12
ARG FROM_NODE_VERSION="node:${NODE_VERSION}-slim"
FROM $FROM_NODE_VERSION

# Update Container Dependencies
RUN apt update && apt upgrade -y && apt install -y nano dos2unix

# Install Vue CLI and Sentry CLI globally
RUN npm -g i @vue/cli @sentry/cli dotenv --unsafe-perm

WORKDIR /app

# Expose Port For Nuxt.JS
EXPOSE 3000

# Copy Application Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN dos2unix /entrypoint.sh
RUN chmod 555 /entrypoint.sh

# Start Container
ENTRYPOINT ["/entrypoint.sh"]
CMD []