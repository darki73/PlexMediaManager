# Plex Media Manager  
  
## Features for Server Administrators  
  
- Ability to manage users and groups    
- Ability to view mounted disks, their statistics and perform necessary operations on them    
- Ability to view application logs    
- Ability to download new / update existing series and movies using provided "Indexers"    
- More other features  
  
  
## Features for Users  
 - Ability to request access to Plex Media Server  
 - Ability to request certain movies / series  
 - Ability to be redirected to Plex when user wants to watch selected item  
 - More other features!  
  
## Getting Started  

### 0. System Requirements

|  |**Minimal**|**Recommended**|
|--|--|--|
|**Processor**|4 Cores|8 Cores|
|**Memory**|4096 MB|~7000 MB|
  
If you are opting for the **minimal** spec, it is recommended to edit the `volumes/backend/config/horizon.php` and lower down the `processes` variables to **1**, you can also try to change the `memory` variables to something like `300` or lower, however, it is not recommended to pick a value lower than `300`.

**Domain names for the application**
If you are familiar with the `docker-compose.yml` and can suite yourself with the variables presented there and not simply `Ctrl + R` the `REPLACE_WITH_DOMAIN_NAME`, you can skip this part, otherwise, if you are  just `Ctrl + R` the variable mentioned before, then these are the A (AAAA) records you have to create on **YOUR** DNS provider, Cloudflare for example. ***(assuming you have also opted for the Traefik)***

| Service | Domain Name |
|--|--|
| **traefik** | **traefik**.REPLACE_WITH_DOMAIN_NAME |
| **torrent** | **torrent**.REPLACE_WITH_DOMAIN_NAME |
| **jackett** | **jackett**.REPLACE_WITH_DOMAIN_NAME |
| **frontend** | **media**.REPLACE_WITH_DOMAIN_NAME |
| **backend** | **media-api**.REPLACE_WITH_DOMAIN_NAME |
| **websockets** | **media-sockets**.REPLACE_WITH_DOMAIN_NAME |


### 1. Cloning the base application  
```bash  
git clone https://github.com/darki73/PlexMediaManager.git plexmediamanager  
```  
### 2. Copying necessary files  
  
```bash  
cp docker-compose.yml.example docker-compose.yml  
cp move-files.sh.example move-files.sh  
cp settings.yml.example settings.yml  
```  
### 3. Picking services  (docker-compose.yml)
#### Traefik  
There are two options in running Traefik  
  
 1. Traefik which is provided inside the applications `docker-compose.yml` file  
 2. Running external Traefik (This requires you to add necessary middlewares to your Traefik installation). This also requires Traefik 2, and wont work with Traefik releases prior version.  
  
**Running provided Traefik**  
Simply remove the comments `#` from the **Traefik** service and change all `REPLACE_WITH_DOMAIN_NAME` values.  
  
**Running external Traefik**  
Simply add this bit of code at the top definition of `networks`  
```yaml  
traefik_proxy:  # this  is the name of the Traefik network created by you  
  external: true  
```  
#### Jackett  
There are two options in running Jackett.  
  
 1. Jackett which is provided inside the applications `docker-compose.yml` file  
 2. Running external Jackett  
  
**Running provided Jackett**  
Simply remove the comments `#` from the **Jackett** service and change all `REPLACE_WITH_DOMAIN_NAME` values.  
  
#### QBitTorrent aka Torrent  
Remove the comments from the `torrent` service and replace `REPLACE_WITH_DOMAIN_NAME` with your actual domain name 

### Be sure to replace `REPLACE_WITH_DOMAIN_NAME` entries in the `docker-compose.yml` file with your  domain name and  `REPLACE_WITH_DOMAIN` in the `move_files.sh` file.

### 4. Populating the `settings.yml`  
```yaml  
jackett:    
  version: v2.0  # This is the Jackett API version, leave it as is if you dont know what this means  
  url: jackett  # URL to Jackett server, if you have your own installation, provider the full URL  
  key:  # Jackett API key, you will receive it upon first visit of https://jackett.DOMAIN_NAME  
  timeout: 10.0    
  max_redirects: 5    
    
proxy:  # Proxy configuration, leave it as is if you dont plan to use proxy  
  type: socks5    
  host: 127.0.0.1    
  port: 1080    
  username: null    
  password: null    
    
search:    
  languages:    
    - en    
    - ru    
    
media:
  tmdb_api_version: 3  # Just dont touch it, i will issue a warning if you will need to change it
  tmdb_api_key:  # API Key for the TheMovieDatabase (https://www.themoviedb.org), can be obtained on your account page  
  plex_url:  # URL for your plex installation (without http:// or https://)  
  plex_api_key:    # Instructions can be found here (https://support.plex.tv/articles/204059436-finding-an-authentication-token-x-plex-token)  
    
storage:    
  preferred: drive_name_1 # Name of the preferred drive (the one which is defined in the plex mounts section)  
  threshold:    
    value: 500  # With the configuration as below, this means that when there is 500 GB or less on the drive, next drive will be selected  
    units: GB    
    percentage: false    
  mounts:    
    plex:    
       drive_name_1: /drive_name_1/path/to/the/plex/library/on/that/drive  
       drive_name_2: /drive_name_2/path/to/the/plex/library/on/that/drive  
    torrent:  /path/to/the/folder/inside/applications/root/volumes/torrent/completed # ./volumes/torrent/completed  
  process_only:    
    - avi    
    - mkv    
    - mp4    
    - m4v    
    
torrent:    
  url: torrent  # URL to the torrent client, leave it as is if you are using torrent client bundled with this application  
  username:  # Username used for the web authentication on the torrent client  
  password: ""  # Password used for the web authentication on the torrent client  
  ignore_parts:    
    - remux  
```  
You also have to edit the `docker-compose.yml` file and add the mounts there. Here are the examples for the 2 services (if you are running your own instance of QBitTorrent, then you only need to edit the `&app-service app: &app-service-template` service)

**&app-service app: &app-service-template**
```yaml
&app-service app: &app-service-template  
  image: registry.freedomcore.ru/darki73/plexmediamanager/application:latest  
  user: "${USER_ID:-1000}:${GROUP_ID:-1000}"  
  restart: always  
  volumes:  
    - home-dir:/home/user  # This one is the default one, dont touch it
    - ./volumes/backend:/app:rw    # This one is the default one, dont touch it
    - .git:/app/.git:ro    # This one is the default one, dont touch it
    - ./settings.yml:/app/storage/app/private/settings.yml:ro  # This one is the default one, dont touch it
    - /drive_name_1/path/to/the/plex/library/on/that/drive/series:/app/storage/app/private/plex/series/drive_name_1
    - /drive_name_2/path/to/the/plex/library/on/that/drive/series:/app/storage/app/private/plex/series/drive_name_2
    - /drive_name_1/path/to/the/plex/library/on/that/drive/movies:/app/storage/app/private/plex/movies/drive_name_1
    - /drive_name_2/path/to/the/plex/library/on/that/drive/movies:/app/storage/app/private/plex/movies/drive_name_2
    - /drive_name/path/to/qbittorrent/completed/series:/app/storage/app/private/torrent/series
    - /drive_name/path/to/qbittorrent/completed/movies:/app/storage/app/private/torrent/movies
```

### 5.  Installing Application  
  
```bash  
make init
```

### 6. Starting application

```bash
make up
```