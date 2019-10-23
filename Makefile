#!/usr/bin/make

SHELL = /bin/sh

REGISTRY_HOST = registry.freedomcore.ru
REGISTRY_PATH = darki73/plexmediamanager
IMAGES_PREFIX := $(shell basename $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST)))))

PUBLISH_TAGS = latest
PULL_TAG = latest

APP_IMAGE = $(REGISTRY_HOST)/$(REGISTRY_PATH)/application
APP_IMAGE_LOCAL_TAG = $(IMAGES_PREFIX)_application
APP_IMAGE_DOCKERFILE = ./configuration/application/Dockerfile
APP_IMAGE_CONTEXT = ./configuration/application

FRONTEND_IMAGE = $(REGISTRY_HOST)/$(REGISTRY_PATH)/frontend
FRONTEND_IMAGE_LOCAL_TAG = $(IMAGES_PREFIX)_frontend
FRONTEND_IMAGE_DOCKERFILE = ./configuration/frontend/Dockerfile
FRONTEND_IMAGE_CONTEXT = ./configuration/frontend

NGINX_IMAGE = $(REGISTRY_HOST)/$(REGISTRY_PATH)/nginx
NGINX_IMAGE_LOCAL_TAG = $(IMAGES_PREFIX)_nginx
NGINX_IMAGE_DOCKERFILE = ./configuration/nginx/Dockerfile
NGINX_IMAGE_CONTEXT = ./configuration/nginx

APP_CONTAINER_NAME := app
FRONTEND_CONTAINER_NAME := frontend

docker_bin := $(shell command -v docker 2> /dev/null)
docker_compose_bin := $(shell command -v docker-compose 2> /dev/null)

all_images =	$(APP_IMAGE) \
				$(APP_IMAGE_LOCAL_TAG) \
				$(FRONTEND_IMAGE) \
				$(FRONTEND_IMAGE_LOCAL_TAG) \
				$(NGINX_IMAGE) \
				$(NGINX_IMAGE_LOCAL_TAG)

.PHONY: help pull build push login test clean \
		app-pull app app-push \
		front-pull front front-push \
		nginx-pull nginx nginx-push \
		up down restart shell install init
.DEFAULT_GOAL := help

help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo "\n  Allowed for overriding next properties:\n\n\
    	    PULL_TAG - Tag for pulling images before building own\n\
    	              ('latest' by default)\n\
    	    PUBLISH_TAGS - Tags list for building and pushing into remote registry\n\
    	                   (delimiter - single space, 'latest' by default)\n\n\
    	  Usage example:\n\
    	    make PULL_TAG='v1.2.3' PUBLISH_TAGS='latest v1.2.3 test-tag' app-push"

# --- [ Application ] -------------------------------------------------------------------------------------------------

app-pull: ## Application - pull latest Docker image (from remote registry)
	-$(docker_bin) pull "$(APP_IMAGE):$(PULL_TAG)"

app: app-pull ## Application - build Docker image locally
	$(docker_bin) build \
	  --cache-from "$(APP_IMAGE):$(PULL_TAG)" \
	  --tag "$(APP_IMAGE_LOCAL_TAG)" \
	  -f $(APP_IMAGE_DOCKERFILE) $(APP_IMAGE_CONTEXT)

app-push: app-pull ## Application - tag and push Docker image into remote registry
	$(docker_bin) build \
	  --cache-from "$(APP_IMAGE):$(PULL_TAG)" \
	  --build-arg INSTALL_PHPREDIS=true \
	  --build-arg INSTALL_PCNTL=true \
	  --build-arg INSTALL_BCMATH=true \
	  --build-arg INSTALL_GMP=true \
	  --build-arg INSTALL_EXIF=true \
	  --build-arg INSTALL_MYSQLI=true \
	  --build-arg INSTALL_INTL=true \
	  --build-arg INSTALL_GHOSTSCRIPT=true \
	  --build-arg INSTALL_IMAGE_OPTIMIZERS=true \
	  --build-arg INSTALL_IMAGEMAGICK=true \
	  --build-arg INSTALL_YAML=true \
	  $(foreach tag_name,$(PUBLISH_TAGS),--tag "$(APP_IMAGE):$(tag_name)") \
	  -f $(APP_IMAGE_DOCKERFILE) $(APP_IMAGE_CONTEXT);
	$(foreach tag_name,$(PUBLISH_TAGS),$(docker_bin) push "$(APP_IMAGE):$(tag_name)";)

# --- [ Frontend ] -------------------------------------------------------------------------------------------------------

front-pull: ## Frontend - pull latest Docker image (from remote registry)
	-$(docker_bin) pull "$(FRONTEND_IMAGE):$(PULL_TAG)"

front: front-pull ## Frontend - build Docker image locally
	$(docker_bin) build \
	  --cache-from "$(FRONTEND_IMAGE):$(PULL_TAG)" \
	  --tag "$(FRONTEND_IMAGE_LOCAL_TAG)" \
	  -f $(FRONTEND_IMAGE_DOCKERFILE) $(FRONTEND_IMAGE_CONTEXT)

front-push: front-pull ## Frontend - tag and push Docker image into remote registry
	$(docker_bin) build \
	  --cache-from "$(FRONTEND_IMAGE):$(PULL_TAG)" \
	  $(foreach tag_name,$(PUBLISH_TAGS),--tag "$(FRONTEND_IMAGE):$(tag_name)") \
	  -f $(FRONTEND_IMAGE_DOCKERFILE) $(FRONTEND_IMAGE_CONTEXT);
	$(foreach tag_name,$(PUBLISH_TAGS),$(docker_bin) push "$(FRONTEND_IMAGE):$(tag_name)";)

# --- [ Nginx ] -------------------------------------------------------------------------------------------------------

nginx-pull: ## Nginx - pull latest Docker image (from remote registry)
	-$(docker_bin) pull "$(NGINX_IMAGE):$(PULL_TAG)"

nginx: nginx-pull ## Nginx - build Docker image locally
	$(docker_bin) build \
	  --cache-from "$(NGINX_IMAGE):$(PULL_TAG)" \
	  --tag "$(NGINX_IMAGE_LOCAL_TAG)" \
	  -f $(NGINX_IMAGE_DOCKERFILE) $(NGINX_IMAGE_CONTEXT)

nginx-push: nginx-pull ## Nginx - tag and push Docker image into remote registry
	$(docker_bin) build \
	  --cache-from "$(NGINX_IMAGE):$(PULL_TAG)" \
	  $(foreach tag_name,$(PUBLISH_TAGS),--tag "$(NGINX_IMAGE):$(tag_name)") \
	  -f $(NGINX_IMAGE_DOCKERFILE) $(NGINX_IMAGE_CONTEXT);
	$(foreach tag_name,$(PUBLISH_TAGS),$(docker_bin) push "$(NGINX_IMAGE):$(tag_name)";)

# ---------------------------------------------------------------------------------------------------------------------

pull: app-pull nginx-pull front-pull ## Pull all Docker images (from remote registry)

build: app front nginx ## Build all Docker images

push: app-push front-push nginx-push ## Tag and push all Docker images into remote registry

login: ## Log in to a remote Docker registry
	@echo $(docker_login_hint)
	$(docker_bin) login $(REGISTRY_HOST)

clean: ## Remove images from local registry
	-$(docker_compose_bin) down -v
	$(foreach image,$(all_images),$(docker_bin) rmi -f $(image);)

# --- [ Development tasks ] -------------------------------------------------------------------------------------------

---------------: ## ---------------

up: ## Start all containers (in background) for development
	$(docker_compose_bin) up --no-recreate -d

down: ## Stop all started for development containers
	$(docker_compose_bin) down

restart: up ## Restart all started for development containers
	$(docker_compose_bin) restart

shell: up ## Start shell into application container
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" /bin/sh

install-up: ## Start containers for installation
	$(docker_compose_bin) up -d --no-recreate redis
	$(docker_compose_bin) up -d --no-recreate database
	$(docker_compose_bin) up -d --no-recreate app
	$(docker_compose_bin) up -d --no-recreate php-fpm
	$(docker_compose_bin) up -d --no-recreate nginx

install: install-up ## Install application dependencies into application container
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" cp .env.example .env
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" composer install --ignore-platform-reqs --no-interaction --ansi --no-suggest
	$(docker_compose_bin) up -d --no-recreate frontend


init: install ## Make full application initialization (install, seed, build assets, etc)
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan pmm:socket-keys --force --no-interaction -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan key:generate --force --no-interaction -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan migrate --force --no-interaction -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan db:seed --force -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan storage:link
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan horizon:install --no-interaction -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan passport:install --force --no-interaction -vvv
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan pmm:restore-all
	$(docker_compose_bin) exec "$(APP_CONTAINER_NAME)" php artisan scout:import
	$(docker_compose_bin) down
