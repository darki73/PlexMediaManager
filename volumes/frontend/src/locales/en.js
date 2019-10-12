export default {
    common: {
        all: 'All',
        total: 'Total',
        free: 'Free',
        refresh: 'Refresh',
        cancel: 'Cancel',
        close: 'Close',
        apply: 'Apply',
        delete: 'Delete',
        update: 'Update',
        fields: {
            email: {
                label: 'E-Mail',
                placeholder: 'Please enter your E-Mail address'
            },
            password: {
                label: 'Password',
                placeholder: 'Please enter your password'
            }
        },
        remember_me: 'Remember Me',
        log_in: 'Log In',
        create_account: 'Create Account',
        clear_form: 'Clear Form',
        log_out: 'Log Out',
        account: 'Account',
        log_in_with_google: 'Sign in with Google',
        logged_in_with_google: 'Signed in with Google',
        log_in_with_plex: 'Sign in with Plex',
        logged_in_with_plex: 'Signed in with Plex',
    },
    notification: {
        request: {
            1: 'Movie',
            0: 'Series',
            created: 'User {username} has requested {type} `{title} ({year})` at {date}'
        }
    },
    errors: {
        api: {
            account: {
                invalid_credentials: 'You have provided invalid credentials',
                not_administrator: 'Your account does not belongs to \'Administrators\' group'
            }
        }
    },
    search: {
        all_providers: 'All',
        provider_title: 'Search Provider',
        provider_placeholder: 'Please select search provider',
        type_title: 'Media Type',
        type_placeholder: 'Media we are looking for',
        query_title: 'Search Query',
        query_placeholder: 'Please start typing for search to begin',
        filter_title: 'Filter Results',
        filter_placeholder: 'Please select items you want to show',
        categories: {
            all: 'All',
            movies: 'Movies',
            series: 'Series',
            music: 'Music',
            tv: 'Series',
            movie: 'Movies'
        },
        genres: 'Genres',
        release_date: 'Release Date',
        average_rating: 'Rating',
        order: 'Order',
        watch: 'Watch',
        requested: 'Request Pending',
        request_denied: 'Request Denied',
        searching: 'Fetching items which match your query...',
        no_results: 'We were unable to find items matching your query',
        common: {
            label: 'Search',
            placeholder: 'Please start typing your search query',
        }
    },
    dashboard: {
        server: {
            card_title: 'System Information',
            os: {
                version: 'OS Version',
                build: 'Build Date: {date}'
            },
            processor: {
                processor: 'Processor',
                model: '{vendor} {model} @ {frequency} ({cores} cores / {threads} threads)'
            },
            memory: {
                total: 'Total Memory',
                available: 'Available Memory'
            },
            uptime: 'Uptime',
            updates: {
                available: 'Updates Available',
                none: 'No Updates'
            }
        },
        network: {
            card_title: 'Network Information',
            backend: {
                domain: 'Backend Domain',
                remote_ip: 'Backend Remote IP',
                local_ip: 'Backend Local IP',
            },
            frontend: {
                domain: 'Frontend Domain',
                remote_ip: 'Frontend Remote IP',
                local_ip: 'Frontend Local IP',
            },
            dns: 'Nameserver #'
        },
        menu: {
            dashboard: 'Dashboard',
            account: {
                accounts: 'Accounts',
                groups: 'Groups',
                users: 'Users'
            },
            indexers: 'Indexers',
            storage: {
                storage: 'Storage',
                disks: 'Disks',
                mounts: 'Mounts'
            },
            logs: {
                logs: 'System Logs',
                all: 'All',
                info: 'Info',
                errors: 'Errors',
                exceptions: 'Exceptions',
                warnings: 'Warnings'
            },
            requests: {
                // TODO: Convert to computed property so the number of requests can be shown
                requests: 'Requests',
                all: 'All',
                series: 'Series',
                movies: 'Movies'
            },
            torrents: {
                torrents: 'Torrents',
                list: 'Active Downloads',
                categories: 'Categories',
                add_torrent: 'Add Torrent'
            },
            search: 'Search',
            settings: 'Settings',
            user: 'User Panel'
        },
        account: {
            users: {
                users: 'Users',
                headers: {
                    id: 'ID',
                    username: 'Username',
                    email: 'E-Mail',
                    email_verified_at: 'Verified On',
                    created_at: 'First Created',
                    updated_at: 'Last Updated',
                    avatar: 'Avatar',
                    role: 'Role',
                    actions: 'Actions'
                },
                update: {
                    title: 'Updating: {username}'
                },
                delete: {
                    title: 'Deleting: {username}',
                    message: 'Are you sure you want to delete selected user and all data associated with that user?',
                    warning: 'This action cannot be reverted, so please, think twice before deleting a user.'
                }
            },
            groups: {
                groups: 'Groups',
                headers: {
                    id: 'Group ID',
                    name: 'Name',
                    guard: 'Guard Name',
                    permissions: 'Permissions',
                    actions: 'Actions'
                },
                permissions_count: '{count} permissions associated with that role',
                id: {
                    label: 'Group ID',
                    placeholder: 'Please provide ID for the group'
                },
                group: {
                    label: 'Group Name',
                    placeholder: 'Please provide a name for the group'
                },
                guard: {
                    label: 'Group Guard',
                    placeholder: 'Please select group guard'
                }
            }
        },
        storage: {
            disks: {
                disks: 'Disks',
                local_mount: 'Local mount',
                remote_mount: 'Remote mount',
                disk_name: 'Disk name',
                media: {
                    series_count_title: 'Number of Series / Episodes',
                    movies_count_title: 'Number of Movies',
                    size_title: 'Size on Disk',
                    percentage_title: 'Percentage'
                },
                pool_information: 'Pool Information',
                refresh_pool_data: 'Refresh Pool Data'
            }
        },
        logs: {
            headers: {
                id: 'ID',
                message: 'Message',
                environment: 'Environment',
                level: 'Level',
                time: 'Time',
            },
            for_date: 'Logs for: {date}',
            loading_logs: 'Fetching latest logs...',
            no_logs: 'There were no logs found'
        },
        requests: {
            all: {
                table_header: 'All Requests'
            },
            headers: {
                id: 'Request ID',
                type: 'Type',
                title: 'Title',
                year: 'Year',
                username: 'Username',
                status: 'Status',
                created: 'Created On',
                actions: 'Actions'
            },
            table: {
                all: 'All Requests',
                0: 'Series Requests',
                1: 'Movies Requests'
            },
            types: {
                0: 'Series',
                1: 'Movie'
            },
            status_label: 'Request Status',
            delete: {
                title: 'Deleting Request',
                message: 'You are about to delete request for `{type}` with title `{name} ({year})` from `{user}`'
            },
            statuses: {
                0: 'Created',
                1: 'Approved',
                2: 'Declined',
                3: 'Completed'
            }
        },
        torrents: {
            title: 'Active Torrents',
            automatic_update: 'Automatic Update',
            delete: 'Deleting a torrent',
            delete_files: 'Also delete all downloaded files',
            create_category: 'Create Category',
            torrent_categories: 'Create categories for Torrent Client',
            statuses: {
                error: 'Error',
                pausedUP: 'Downloaded Paused',
                pausedDL: 'Paused',
                queuedUP: 'Queued (Seeding)',
                queuedDL: 'Queued (Downloading)',
                uploading: 'Seeding',
                stalledUP: 'Stalled',
                checkingUP: 'Finishing',
                checkingDL: 'Finishing',
                downloading: 'Downloading',
                stalledDL: 'Stalled',
                metaDL: 'Starting',
            },
            headers: {
                hash: 'Hash',
                name: 'Name',
                progress: 'Progress',
                speed: 'Speed',
                category: 'Category',
                seeds: 'Seeds',
                state: 'State',
                actions: 'Actions',
            },
            categories: {
                series: 'Series',
                movies: 'Movies',
                music: 'Music'
            },
            create: {
                title: 'Add Torrent',
                select_file: 'Torrent File',
                select_file_placeholder: 'Please select file(s) you want to upload',
                select_category: 'Torrent Category',
                select_category_placeholder: 'Please select appropriate category for this torrent',
            }
        },
        settings: {
            tabs: {
                environment: 'Environment',
                disks: 'Disks',
                proxy: 'Proxy'
            },
            loading_settings: 'Fetching latest application settings...',
            environment: {
                name: {
                    label: 'Application Name',
                    placeholder: 'Please provide Application Name'
                },
                env: {
                    label: 'Application Environment',
                    placeholder: 'Please specify current application environment',
                    local: 'Local',
                    production: 'Production'
                },
                key: {
                    label: 'Application Key',
                    placeholder: 'Please specify application key',
                    hint: 'We do not recommend to change this field manually'
                },
                debug: {
                    label: 'Application Debug',
                    placeholder: 'Please specify whether or not you want debug enabled',
                    enabled: 'Enabled',
                    disabled: 'Disabled'
                },
                url: {
                    label: 'Application URL',
                    placeholder: 'Please specify application address'
                }
            },
            disks: {
                drive: 'Disk `{drive}` - {used} / {total} ({percentage}% used)',
                preferred: {
                    label: 'Preferred Disk',
                    placeholder: 'Please select preferred disk',
                    hint: 'Some application settings might override this selection'
                },
                threshold: {
                    label: 'Free Space Threshold',
                    placeholder: 'Please select free space threshold',
                    hint: 'This option will override preferred drive if threshold has been reached'
                },
                type: {
                    label: 'Threshold Type',
                    placeholder: 'Please select threshold type',
                    percentage: 'Percentage',
                    units: 'Units'
                },
                units: {
                    label: 'Threshold Units',
                    placeholder: 'Please specify the units used to determine threshold',
                    kb: 'KB',
                    mb: 'MB',
                    gb: 'GB',
                    tb: 'TB',
                    pb: 'PB'
                }

            },
            proxy: {
                type: {
                    label: 'Proxy Type',
                    placeholder: 'Please select appropriate proxy type'
                },
                host: {
                    label: 'Proxy Host',
                    placeholder: 'Please provide proxy server host'
                },
                port: {
                    label: 'Proxy Port',
                    placeholder: 'Please provide proxy server port'
                },
                username: {
                    label: 'Proxy Username',
                    placeholder: 'Please provide username used for authentication',
                    hint: 'Leave it empty if proxy server does not have authentication enabled'
                },
                password: {
                    label: 'Proxy Password',
                    placeholder: 'Please provide password used for authentication',
                    hint: 'Leave it empty if proxy server does not have authentication enabled'
                }
            }
        },
        indexers: {
            title: 'Application Indexers',
            title_indexer: '{indexer} items',
            indexer: 'Indexer: {indexer}',
            series: 'Series ID: {series}',
            season: 'Season: {season}',
            updated: 'Updated: {updated}',
            loading_series: 'Please wait while we load series information',
            headers: {
                name: 'Indexer',
                class: 'Class',
                items: 'Items Count',
                actions: 'Actions',
                series_id: 'Series ID',
                series_title: 'Series Title',
                series_has_torrent: 'Has Torrent',
                series_created_at: 'Created At',
                series_updated_at: 'Updated At',
            },
            view_items: 'View Items',
            preview: {
                seasons: '{seasons} season | {seasons} seasons',
                episodes: '{episodes} episode | {episodes} episodes',
                released: 'Released on {date}',
                downloaded: 'Downloaded {downloaded} out of {total} episodes',
                episode: 'Episode {number} - {title}',
                status: {
                    download: 'Download',
                    downloaded: 'Downloaded',
                },
                switch: {
                    text: 'Exclude this episode from downloads',
                    download: 'This season will be downloaded',
                    exclude: 'This season will be excluded from download'
                }
            }
        }
    },
    user: {
        menu: {
            admin: 'Administrative Panel',
            home: 'Home',
            search: 'Search',
            requests: {
                requests: '[WIP] Requests',
                all: 'All',
                movies: 'Movies',
                series: 'Series'
            },
            top: {
                top: '[WIP] Top Picks',
                movies: 'Movies',
                series: 'Series'
            }
        },
        account: {
            login: {

            },
            create: {
                username: {
                    label: 'Username',
                    placeholder: 'Please provide desired Username'
                },
                email: {
                    label: 'E-Mail',
                    placeholder: 'Please provide your E-Mail address'
                },
                password: {
                    label: 'Password',
                    placeholder: 'Please enter password'
                },
                password_confirmation: {
                    label: 'Password Confirmation',
                    placeholder: 'Please confirm password'
                }
            }
        },
        plex: {
            servers: {
                servers: 'Available Servers',
                fetching: 'Loading information about available servers...',
                no_servers: 'We were unable to find any servers associated with your account.',
                local: 'Local Server',
                remote: 'Remote Server',
                version: 'Version: {version}',
                last_updated: 'Last Updated: {date}',
                ping: 'Server Ping: {ping}'
            }
        }
    }
};
