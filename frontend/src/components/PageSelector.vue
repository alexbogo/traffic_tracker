<template>
  <div class="page-selector">
    <label for="pageSelect" class="form-label">Select Page</label>
    <select
      id="pageSelect"
      class="form-select"
      v-model="selectedPageId"
      @change="handlePageChange"
      :disabled="loading || pages.length === 0"
    >
      <option value="" disabled>
        {{ loading ? 'Loading pages...' : 'Choose a page' }}
      </option>
      <option v-for="page in pages" :key="page.id" :value="page.id">
        {{ page.url }}
      </option>
    </select>
    <small v-if="pages.length === 0 && !loading" class="text-muted">
      No pages tracked yet. Visit some demo pages to see them here.
    </small>
  </div>
</template>

<script>
import { ref, watch } from 'vue';

export default {
  name: 'PageSelector',
  props: {
    pages: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
    modelValue: {
      type: [Number, String],
      default: '',
    },
  },
  emits: ['update:modelValue', 'page-selected'],
  setup(props, { emit }) {
    const selectedPageId = ref(props.modelValue);

    // Watch for external changes to modelValue
    watch(
      () => props.modelValue,
      (newValue) => {
        selectedPageId.value = newValue;
      }
    );

    // Watch for pages array changes and auto-select first page
    watch(
      () => props.pages,
      (newPages) => {
        if (newPages.length > 0 && !selectedPageId.value) {
          selectedPageId.value = newPages[0].id;
          handlePageChange();
        }
      },
      { immediate: true }
    );

    const handlePageChange = () => {
      emit('update:modelValue', selectedPageId.value);
      const selectedPage = props.pages.find((p) => p.id == selectedPageId.value);
      emit('page-selected', selectedPage);
    };

    return {
      selectedPageId,
      handlePageChange,
    };
  },
};
</script>

<style scoped>
.page-selector {
  margin-bottom: 20px;
}
</style>
