import {
  HomeIcon,
  TicketIcon,
  MessageCircleIcon,
  FileTextIcon,
  SettingsIcon,
  ChartLineIcon,
  ListIcon,
  CategoryIcon,
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
  requiresRole?: string;
  requiresPermission?: string;
}

const sidebarItem: menu[] = [
  {header: 'Administração'},
  {
        title: 'Tenants',
        icon: HomeIcon,
        to: '/plataforma/tenants',
        requiresPermission: 'tenants.visualizar',
      },
      {
        title: 'Usuários',
        icon: HomeIcon,
        to: '/plataforma/usuarios',
        requiresPermission: 'usuarios.visualizar',
      },
      {
        title: 'Perfis',
        icon: HomeIcon,
        to: '/plataforma/perfis',
        requiresPermission: 'perfis.visualizar',
      },
  { header: 'CSI - Customer Success Intelligence' },
  {
    title: 'Clientes',
    icon: TicketIcon,
    to: '/csi/clientes',
    requiresPermission: 'clientes.visualizar',
  },
  {
    title: 'Interações',
    icon: MessageCircleIcon,
    to: '/csi/interacoes',
    requiresPermission: 'interacoes.visualizar',
  },
  {
    title: 'Conectores',
    icon: SettingsIcon,
    to: '/csi/conectores',
    requiresPermission: 'conectores.visualizar',
  },
  {
    title: 'Tipos de Interação',
    icon: CategoryIcon,
    to: '/csi/tipos-interacao',
    requiresPermission: 'tipos_interacao.visualizar',
  },
  { header: 'Monitoramento' },
  {
    title: 'Logs do Sistema',
    icon: ListIcon,
    to: '/logs',
  },
  {
    title: 'Dashboard de Logs',
    icon: ChartLineIcon,
    to: '/logs/dashboard',
  },

];

export default sidebarItem;
