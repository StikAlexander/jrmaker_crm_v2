import preset from "../../../../vendor/filament/filament/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: "#fce8e6",     // Rojo muy claro
                    100: "#f8c5c3",    // Rojo claro
                    200: "#f39f9d",    // Rojo más claro
                    300: "#ef7875",    // Rojo terracota claro
                    400: "#ea5a5b",    // Rojo terracota medio
                    500: "#ab534f",    // Rojo terracota, color principal
                    600: "#933F3A",    // Rojo más oscuro para hover
                    700: "#80312e",
                    800: "#6a2522",
                    900: "#541817",    // Rojo terracota oscuro
                },
                secondary: {
                    50: "#e8ebf1",     // Azul muy claro
                    100: "#bfc4d1",    // Azul claro
                    200: "#97a0b2",    // Azul grisáceo
                    300: "#6e7b92",    // Azul grisáceo más oscuro
                    400: "#4f5d73",    // Azul medio
                    500: "#354153",    // Azul oscuro, color secundario para textos y detalles
                    600: "#2b3644",
                    700: "#222c36",
                    800: "#191f29",
                    900: "#10141b",    // Azul más oscuro para detalles menores
                },
                gray: {
                    50: "#f5f5f5",
                    100: "#ebebeb",
                    200: "#e0e0e0",
                    300: "#d6d6d6",
                    400: "#cbcbcb",
                    500: "#9c9ea5",    // Gris claro con toque azulado
                    600: "#838588",
                    700: "#6a6b6c",
                    800: "#515253",
                    900: "#383838",    // Gris oscuro, si es necesario
                },
                success: "#12c3b2",    // Verde azulado (si realmente lo necesitas)
                danger: "#c71d51",     // Rojo intenso para alertas y errores
                info: "#710cc3",       // Púrpura vibrante para información adicional
                warning: "#ffba5d",    // Amarillo cálido para advertencias y alertas
            },
        },
    },
};
