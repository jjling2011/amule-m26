// src/locales/en.js
export default {
    app: {
        order: "Order",
        filter: "Filter",
        ascending: "Asce",
        descending: "Desc",
    },
    theme: {
        title: "Theme",
        light: "Light",
        dark: "Dark",
    },
    nav: {
        download: "Download",
        server: "Server",
        search: "Search",
        prefs: "Preference",
        logs: "Logs",
        about: "About",
    },
    dialogs: {
        ok: "Ok",
        cancel: "Cancel",
    },
    ed2k: {
        no_link: "There is no link to download!",
        serv_recv: "Server receive",
        links_dot: "links.",
        links: "links",
    },
    logs: {
        cat: "Cat.",
        clear_all_logs: "Clear logs",
        autoscroll: "Auto scroll",
    },
    search: {
        please_select_files: "Please select files to download.",
        download_to: "Download to",
        name: "Name",
        size: "Size",
        sources: "Sources",
        none: "None",
    },
    servers: {
        invalid_ipv4: "Invalide IPv4 address",
        bootstrap_kad: "Bootstrap KAD",
        add_new_server: "Add Server",
        require_three_param: "Require 3 params.",
        connect: "Connect",
        disconnect_server: "Disconnect Server",
        disconnect_kad: "Disconnect KAD",
        connect_kad: "Connect KAD",
        disconnect: "Disconnect",
        remove: "Remove",
        confirmRemoveServer: "Remove server?",
        active: "Active",
        action: "Action",
        name: "Name",
        address: "Address",
        description: "Description",
        users: "Users",
        files: "Files",
    },
    download: {
        move_to: "Move to",
        task_action: "Action",
        copy_success: "Copied to clipboard.",
        copy_fail: "Fail to copy to clipboard.",
        pause: "Pause",
        resume: "Resume",
        cancel: "Remove",
        priodown: "Prio. Down",
        prioup: "Prio. Up",
        copy_link: "Copy Link",
        remove: "Remove ",
        tasks: " tasks?",
        please_select_tasks: "Please select tasks.",
        unsupported_cmd: "Unsupported command:",
        name: "Name",
        cat: "Catgory",
        size: "Size",
        speed: "Speed",
        prio: "Priority",
        status: "Status",
        complete: "Complete",
    },
    prefs: {
        logout: "Log out",
        save: "Save",
        key: "Key",
        value: "Value",
        description: "Description",
        readonly: "(Readonly)",
        dtable: {
            nick: "Nickname",
            connection: {
                max_line_up_cap: "Max upload rate (for statistics only)",
                max_line_down_cap: "Max download rate (for statistics only)",
                max_up_limit: "Max upload rate",
                max_down_limit: "Max download rate",
                slot_alloc: "Slot allocation",
                tcp_port: "TCP port",
                udp_port: "UDP port",
                udp_dis: "Disable UDP connections",
                max_file_src: "Max sources per file",
                max_conn_total: "Max total connections (total)",
                autoconn_en: "Autoconnect at startup",
                reconn_en: "Reconnect when connection lost",
                network_ed2k: "Enable ED2K network",
                network_kad: "Enable Kademlia network",
            },
            files: {
                ich_en: "I.C.H. active",
                aich_trust: "AICH trusts every hash (not recommended)",
                new_files_paused: "Add files to download queue in pause mode",
                new_files_auto_dl_prio: "Added download files have auto priority",
                new_files_auto_ul_prio: "New shared files have auto priority",
                extract_metadata: "Extract metadata tags",
                alloc_full: "Alloc full disk space for .part files",
                check_free_space: "Check free space",
                min_free_space: "Minimum free space (Mb)",
            },
            webserver: {
                use_gzip: "Use gzip compression",
                autorefresh_time: "Page refresh interval",
            },
            servers: {
                add_from_server: "Add server from connected server",
                add_from_client: "Add server from connected client",
            },
        },
    },
    about: {
        stats: "Stats.",
        note: `Filter:
hello    include string "hello"
-world   without string "world"
^hello   starts with "hello"
-^world  not starts with "world"
#music   category is "music"
-#music  category is not "music"
{'@'}        selected
>10      size bigger than 10 MiB
<200     size smaller than 200 MiB
>20%     download complete ratio more than 20%
<80%     download complete ratio less than 80%

Filter keywords are seperated by space.


Tips:
Click app logo to scroll to top.

GitHub:
https://github.com/jjling2011/amule-m26/

Credits:
https://github.com/ngosang/docker-amule/
https://github.com/MatteoRagni/AmuleWebUI-Reloaded/
https://github.com/amule-org/amule/
https://github.com/amule-project/amule/

2026-06`,
    },
}
