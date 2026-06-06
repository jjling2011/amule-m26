<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="/favicon.ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Amule Login</title>
    <style type="text/css">
        /* amuleweb can not load other css file while login */

        /* themes.css */

        /* light mode */
        :root {
            --bg-color: #f0e7de;
            --fg-color: black;
            --dlg-mask: rgba(125, 125, 125, 0.7);
            --btn-color: var(--bg-color);
            --btn-hover: rgb(226 209 180);

            --sidebar-bg-color: #2c3e50;
            --table-header-color: rgb(226 209 180);
            --table-row-even: rgb(228 220 207);
        }

        /* dark mode */
        [data-theme="dark"] {
            --bg-color: #17232f;
            --fg-color: whitesmoke;
            --btn-color: rgb(36 51 82);
            --btn-hover: rgb(77 102 152);
            --table-header-color: rgb(77 102 152);
            --table-row-even: rgb(36 48 71);
        }

        * {
            /* background-color: var(--bg-color); */
            color: var(--fg-color);
        }

        /* 4. 页面中使用 CSS 变量 */
        body {
            background-color: var(--sidebar-bg-color);
            color: var(--fg-color);
            transition: all 0.3s ease;
            /* 增加平滑过渡效果 */
        }

        input,
        select {
            color: black;
        }

        button {
            border: 1px solid gray;
            background-color: var(--btn-color);
            color: var(--fg-color);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
        }

        button:hover {
            background-color: var(--btn-hover);
        }

        /* dialogs.css */
        .dialog-content {
            width: 95%;
            display: flex;
            margin: 0.25rem auto;
        }

        #dialog-select,
        #dialog-loading,
        #dialog-alert,
        #dialog-confirm,
        #dialog-prompt {
            z-index: 1000;
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            background-color: var(--dlg-mask);
            display: none;
        }

        #dialog-loading-window,
        #dialog-select-window,
        #dialog-alert-window,
        #dialog-confirm-window,
        #dialog-prompt-window {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid gray;
            background-color: var(--bg-color);
            margin: auto auto;
            position: relative;
            top: 30%;
            width: 80%;
            max-width: 25rem;
            display: flex;
            flex-direction: column;
        }

        #dialog-loading pre,
        #dialog-alert-window pre,
        #dialog-confirm-window pre,
        #dialog-prompt-window pre {
            word-break: break-all;
            font-size: 1rem;
            white-space: pre-wrap;
        }

        #dialog-loading div,
        #dialog-select div,
        #dialog-loding div,
        #dialog-alert-window div,
        #dialog-confirm-window div,
        #dialog-prompt-window div {
            justify-content: center;
        }

        #dialog-select-window button,
        #dialog-alert-window button,
        #dialog-confirm-window button,
        #dialog-prompt-window button {
            min-width: 3rem;
            font-size: 0.8rem;
            border-radius: 0.5rem;
            border: solid 1px var(--fg-color);
            margin: 0.125rem 0.25rem;
            padding: 0.25rem 1rem;
        }

        #dialog-loading img {
            max-width: 30%;
        }

        #dialog-prompt-window input {
            height: 1.5rem;
            font-size: 1rem;
            flex-grow: 1;
        }

        #dialog-select-window select {
            height: 1.5rem;
            flex-grow: 1;
        }


        /* 4. 页面中使用 CSS 变量 */
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            /* 增加平滑过渡效果 */
        }

        #dialog-prompt {
            display: block;
        }

        #dialog-prompt-window {
            align-items: center;
        }

        #dialog-prompt-window div {
            justify-content: center;
        }

        #dialog-prompt-submit {
            cursor: pointer;
            height: 2rem;
            border-radius: 0.5rem;
            margin: 0.125rem 0.25rem;
            background-color: var(--btn-color);
            font-size: 1rem !important;
            border: 1px solid gray;
            flex-grow: 0 !important;
            padding: 0.5rem 1rem;
            line-height: 1rem;
            height: 2rem !important;
        }

        #dialog-prompt-submit:hover {
            background-color: var(--btn-hover);
        }

        #dialog-prompt-password {
            margin-left: 0.5rem;
            height: 1.5rem;
            font-size: 1rem;
            padding-left: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid gray;
            flex-grow: 1;
        }
    </style>
</head>

<body>
<?php
$ver = m26_get_version();
if(!m26_startswith("amule-m26", $ver)){

    echo "<pre>";
    echo "This web UI require amule-m26 backend.\n";
    
    echo "</pre>";
    echo '<a href="https://github.com/jjling2011/amule-m26/">amule-m26</a>';
}
?>
    <div id="dialog-prompt"
<?php
$ver = m26_get_version();
if(!m26_startswith("amule-m26", $ver)){
    echo  ' style="display:none" ';
}
?> 
    >
        <div id="dialog-prompt-window">
            <img
                src="favicon.ico"
                alt="amule.icon"
                style="width: 5rem; margin-bottom: 1rem" />
            <form action="" method="post" name="login">
                <div class="dialog-content">
                    <span>Admin</span>
                    <input
                        type="password"
                        name="pass"
                        id="dialog-prompt-password"
                        autofocus />
                </div>
                <div class="dialog-content">
                    <input
                        name="submit"
                        type="submit"
                        id="dialog-prompt-submit"
                        value="Submit" />
                </div>
            </form>
        </div>
    </div>
    <script>
        const themeModeStorageKey = "m26-color-theme-name"

        function isSysThemeModeDark() {
            window.matchMedia("(prefers-color-scheme: dark)").matches
        }

        function getCurThemeMode() {
            const mode = localStorage.getItem(themeModeStorageKey)
            if (mode) {
                return mode
            }
            return isSysThemeModeDark() ? "dark" : "light"
        }

        function switchTheme(theme) {
            if (theme === "auto") {
                theme = isSysThemeModeDark() ? "dark" : "light"
            }
            localStorage.setItem(themeModeStorageKey, theme)
            if (theme === "dark") {
                document.documentElement.setAttribute("data-theme", theme)
            } else {
                document.documentElement.removeAttribute("data-theme")
            }
        }

        window.onload = function() {
            const theme = getCurThemeMode()
            switchTheme(theme)
        }
    </script>
</body>

</html>