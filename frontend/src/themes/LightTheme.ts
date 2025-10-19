import type { ThemeTypes } from '@/types/theme/theme';

const LIGHT_THEME: ThemeTypes = {
    name: 'LIGHT_THEME',
    dark: false,
    variables: {
        'border-color': '#E5EAEA'
    },
    colors: {
        primary: '#428BCA',
        secondary: '#7892A1',
        info: '#999999',
        success: '#0C9348',
        warning: '#FCBF10',
        error: '#C13018',
        indigo:'#8763da',
        lightprimary: '#e5f3fb',
        lightinfo:'#e1f5fa',
        lightsecondary: '#e7ecf0',
        lightsuccess: '#dffff3',
        lighterror: '#ffede9',
        lightwarning: '#fff6ea',
        lightindigo:'#f1ebff',
        textPrimary: '#333333',
        textSecondary: '#2A3547',
        borderColor: '#E5EAEA',
        inputBorder: '#DFE5EF',
        containerBg: '#ffffff',
        background: '#E5EAEA',
        hoverColor: '#f6f9fc',
        surface: '#fff',
        grey100: '#707a82',
        grey200: '#111c2d',
        darkbg:'#2a3447',
        bglight:'#f5f8fb',
        bgdark:'#111c2d'
       
    }
};


export { LIGHT_THEME };
