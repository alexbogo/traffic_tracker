<template>
  <div class="date-range-picker">
    <div class="row g-2">
      <!-- Preset Buttons -->
      <div class="col-12 mb-2">
        <div class="btn-group w-100" role="group">
          <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            :class="{ active: selectedPreset === 'today' }"
            @click="selectPreset('today')"
          >
            Today
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            :class="{ active: selectedPreset === 'last7days' }"
            @click="selectPreset('last7days')"
          >
            Last 7 Days
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            :class="{ active: selectedPreset === 'last30days' }"
            @click="selectPreset('last30days')"
          >
            Last 30 Days
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-primary"
            :class="{ active: selectedPreset === 'custom' }"
            @click="selectedPreset = 'custom'"
          >
            Custom
          </button>
        </div>
      </div>

      <!-- Date Display (dd-mm-yyyy format) -->
      <div class="col-md-6">
        <label class="form-label small">Start Date (dd-mm-yyyy)</label>
        <div class="date-display">{{ formatDisplayDate(startDate) }}</div>
        <input
          type="date"
          class="form-control form-control-sm mt-1"
          v-model="startDate"
          @change="handleDateChange"
          :max="endDate"
        />
      </div>
      <div class="col-md-6">
        <label class="form-label small">End Date (dd-mm-yyyy)</label>
        <div class="date-display">{{ formatDisplayDate(endDate) }}</div>
        <input
          type="date"
          class="form-control form-control-sm mt-1"
          v-model="endDate"
          @change="handleDateChange"
          :min="startDate"
          :max="maxDate"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'DateRangePicker',
  emits: ['update:dateRange'],
  setup(props, { emit }) {
    const selectedPreset = ref('last7days');
    const startDate = ref('');
    const endDate = ref('');

    // Max date is today
    const maxDate = computed(() => {
      return new Date().toISOString().split('T')[0];
    });

    // Format date to YYYY-MM-DD
    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    // Convert yyyy-mm-dd to dd-mm-yyyy for display
    const formatDisplayDate = (isoDate) => {
      if (!isoDate) return '';
      const [year, month, day] = isoDate.split('-');
      return `${day}-${month}-${year}`;
    };

    // Select preset date range
    const selectPreset = (preset) => {
      selectedPreset.value = preset;
      const today = new Date();
      const end = new Date(today);

      let start;
      switch (preset) {
        case 'today':
          start = new Date(today);
          break;
        case 'last7days':
          start = new Date(today);
          start.setDate(start.getDate() - 6);
          break;
        case 'last30days':
          start = new Date(today);
          start.setDate(start.getDate() - 29);
          break;
        default:
          return;
      }

      startDate.value = formatDate(start);
      endDate.value = formatDate(end);
      emitDateRange();
    };

    // Handle manual date change
    const handleDateChange = () => {
      selectedPreset.value = 'custom';
      emitDateRange();
    };

    // Emit date range to parent
    const emitDateRange = () => {
      emit('update:dateRange', {
        startDate: startDate.value,
        endDate: endDate.value,
      });
    };

    // Initialize with last 7 days
    selectPreset('last7days');

    return {
      selectedPreset,
      startDate,
      endDate,
      maxDate,
      selectPreset,
      handleDateChange,
      formatDisplayDate,
    };
  },
};
</script>

<style scoped>
.date-range-picker {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
}

.date-display {
  font-size: 0.9em;
  font-weight: 500;
  padding: 6px 10px;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  color: #495057;
}
</style>
