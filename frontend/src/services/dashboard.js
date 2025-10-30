import api from './api';

const dashboardService = {
  /**
   * Get all tracked pages
   */
  async getPages() {
    try {
      const response = await api.get('/pages');
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch pages',
      };
    }
  },

  /**
   * Get statistics for a specific page
   */
  async getPageStats(pageId, params = {}) {
    try {
      const response = await api.get(`/pages/${pageId}/stats`, {
        params: {
          start_date: params.startDate,
          end_date: params.endDate,
          exclude_bots: params.excludeBots ? 1 : 0,
        },
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch statistics',
      };
    }
  },

  /**
   * Get visit history for a specific page
   */
  async getPageVisits(pageId, page = 1, limit = 50) {
    try {
      const response = await api.get(`/pages/${pageId}/visits`, {
        params: { page, limit },
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to fetch visits',
      };
    }
  },
};

export default dashboardService;
