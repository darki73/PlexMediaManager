export default {
    common: {
        all: 'All',
        total: 'Total',
        free: 'Free',
        refresh: 'Refresh',
        cancel: 'Cancel',
        apply: 'Apply',
        delete: 'Delete'
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
        requested: 'Request Pending'
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
            search: 'Search'
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
            }
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
        }
    }
};
