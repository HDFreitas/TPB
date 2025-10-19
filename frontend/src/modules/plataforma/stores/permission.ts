import { defineStore } from 'pinia';
import permissionService from '@/services/permissions/permissions';

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    permissions: [] as any[],
    loading: false,
    error: null as string | null,
    selectedPermission: null as any,
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
  }),
  actions: {
    async fetchPermissions(params?: any) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await permissionService.getAll(params);
        this.permissions = data.data || data;
        this.pagination = {
          current_page: data.current_page || 1,
          per_page: data.per_page || 15,
          total: data.total || 0,
          last_page: data.last_page || 1,
        };
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar permissões';
      } finally {
        this.loading = false;
      }
    },
    async createPermission(payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await permissionService.create(payload);
        await this.fetchPermissions();
      } catch (e: any) {
        this.error = e.message || 'Erro ao criar permissão';
      } finally {
        this.loading = false;
      }
    },
    async updatePermission(id: number, payload: any) {
      this.loading = true;
      this.error = null;
      try {
        await permissionService.update(id, payload);
        await this.fetchPermissions();
      } catch (e: any) {
        this.error = e.message || 'Erro ao atualizar permissão';
      } finally {
        this.loading = false;
      }
    },
    async deletePermission(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await permissionService.delete(id);
        await this.fetchPermissions();
      } catch (e: any) {
        this.error = e.message || 'Erro ao deletar permissão';
      } finally {
        this.loading = false;
      }
    },
    async getPermissionById(id: number) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await permissionService.getById(id);
        this.selectedPermission = data;
      } catch (e: any) {
        this.error = e.message || 'Erro ao buscar permissão';
      } finally {
        this.loading = false;
      }
    },
  },
});
