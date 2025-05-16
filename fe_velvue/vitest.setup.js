import { beforeAll } from "vitest";
import { config } from "@vue/test-utils";

// Mock global components used in the application
beforeAll(() => {
  // Create a global component stub for all PrimeVue components
  config.global.stubs = {
    Message: true,
    Tabs: true,
    TabPanel: true,
    Avatar: true,
    FileUpload: true,
    InputText: true,
    Textarea: true,
    Button: true,
    DataTable: true,
    Column: true,
    ConfirmDialog: true,
  };
});
