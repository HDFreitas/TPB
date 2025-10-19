import {
  HomeIcon,
} from 'vue-tabler-icons';

export interface menu {
  header?: string;
  title?: string;
  icon?: any;
  to?: string;
  chip?: string;
  BgColor?: string;
  chipBgColor?: string;
  chipColor?: string;
  chipVariant?: string;
  chipIcon?: string;
  children?: menu[];
  disabled?: boolean;
  type?: string;
  subCaption?: string;
}

const sidebarItem: menu[] = [
  {header: 'Administração'},
  {
        title: 'Tenants',
        icon: HomeIcon,
        to: '/tenants',
      },
      {
        title: 'Usuários',
        icon: HomeIcon,
        to: '/usuarios',
      },
      {
        title: 'Perfis',
        icon: HomeIcon,
        to: '/perfis',
      },
  { header: 'Monitoramento' },
  {
    title: 'Logs',
    icon: HomeIcon,
    to: '/logs',
  }
  
];

export default sidebarItem;
