const {colors: defaultColors} = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    theme: {
        extend: {
            animation: {
                'fade-in': 'fadeIn .7s ease-in-out',
                'fade-in-left': 'fadeInRight 0.4s ease-in-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(10px)',
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)',
                    },
                },
                fadeInRight: {
                    '0%': {
                        opacity: '0',
                        transform: 'translateX(10px)',
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateX(0)',
                    },
                },
            },
            spacing: {
                '4.5': '1.125rem', // 18px
                '3.5': '0.875rem', // 14px
            },
            borderWidth: {
                'thin': '.5px',
            },
            boxShadow: {
                'custom': '0 2px 4px #d8e1e8',
                'md': '0 2px 4px #d8e1e8',
            },
            fontSize: {
                // 'base': '0.938rem', // 15px
            },
            colors: {
                'light-orange': '#ffc576',
                'light-orange-2': '#efb044',
                'light-orange-3': '#ff9f00',
                'light-orange-4': '#ff9f00',
                'corail': '#ff7f50',
                secondary: {
                    '900': '#ff9f00',
                    '800': '#efb044',
                    '700': '#ffc576',
                },
                'dark': 'hsl(222.2, 84%, 4.9%)',
                'muted': 'hsl(217.2, 32.6%, 17.5%)',
                'dark-variant': '#1c1d27',
                'white-soft': '#f7fafb',
                'dark-soft': '#1f2235',
                'dark-soft-2': '#292c3f',
                'dark-soft-3': '#313552',
                tertiary: {
                  '800': "#586fad",
                  '900': "#4e5e9d",
                  '950': "#3e4c7b",
                },
                primaryOl: {
                    '50': '#fff6ed',
                    '100': '#ffebd5',
                    '200': '#fed3aa',
                    '300': '#fdb474',
                    '400': '#fb9550',
                    '500': '#f86817',
                    '600': '#e94e0d',
                    '700': '#c2380c',
                    '800': '#9a2e12',
                    '900': '#7c2812',
                    '950': '#431107',
                },
                primary: {
                    '50': '#f4f3ff',
                    '100': '#ece9fe',
                    '200': '#dad5ff',
                    '300': '#bfb4fe',
                    '400': '#a089fc',
                    '500': '#8259f9',
                    '600': '#7136f1',
                    '700': '#5d21d2',
                    '800': '#521eb9',
                    '900': '#451b97',
                    '950': '#280e67',
                },
                primary2: {
                    "50": "#f3f1ff",
                    "100": "#ebe5ff",
                    "200": "#d9ceff",
                    "300": "#bea6ff",
                    "400": "#9f75ff",
                    "500": "#843dff",
                    "600": "#7916ff",
                    "700": "#6b04fd",
                    "800": "#5a03d5",
                    "900": "#5d21d2",
                    "950": "#441998",
                },
                primaryd: {
                    "50": "rgb(248 250 252)",
                    "100": "rgb(241 243 245)",
                    "200": "rgb(226 232 240)",
                    "300": "rgb(203 213 225)",
                    "400": "rgb(148 163 184)",
                    "500": "rgb(100 116 139)",
                    "600": "rgb(71 85 105)",
                    "700": "rgb(51 65 85)",
                    "800": "rgb(30 41 59)",
                    "900": "rgb(15 23 42)",
                    "950": "rgb(2 6 23)"
                },
                secondaryOld: {
                    "50": "#f8f9fc",
                    "100": "#f1f3f8",
                    "200": "#e9eef5",
                    "300": "#d8e1ef",
                    "400": "#b6c9e1",
                    "500": "#93b1d3",
                    "600": "#7c9ac0",
                    "700": "#657ea8",
                    "800": "#4e628f",
                    "900": "#3d4e74",
                    "950": "#2b3650"
                },
                danger: {
                    "50": "#fff8f8",
                    "100": "#ffefef",
                    "200": "#ffd7d7",
                    "300": "#ffafaf",
                    "400": "#ff7d7d",
                    "500": "#ff4a4a",
                    "600": "#ff1f1f",
                    "700": "#ff0b0b",
                    "800": "#e60000",
                    "900": "#c50000",
                    "950": "#8c0000"
                }
            }
        },
        fontFamily: {
            'inter': ['Inter Var'],
            'plusJakartaSans': ['Plus Jakarta Sans'],
            'ibmPlexSans': ['IBM Plex Sans'],
            'hanken-grotesk': ['Hanken Grotesk'],
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}

