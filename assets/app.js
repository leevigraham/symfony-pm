import './styles/app.css';
import '@symfony/ux-live-component/dist/live.min.css';

// import "@hotwired/turbo";

import { Application } from "@hotwired/stimulus";
import LiveController from '@symfony/ux-live-component';
import DataTableController from './controllers/data_table_controller.js';
import ChipController from "./controllers/chip_controller.js";
import AppController from "./controllers/app_controller.js";
import ContextMenuController from "./controllers/context_menu_controller.js";
import ContextMenuTriggerController from "./controllers/context_menu_trigger_controller.js";
import DrawerManagerController from "./controllers/drawer_manager_controller.js";
import ContentLoaderController from "./controllers/content_loader_controller.js";

const app = Application.start();
app.debug = true;
app.register('app', AppController);
app.register('live', LiveController);
app.register('chip', ChipController);
app.register('context-menu', ContextMenuController);
app.register('context-menu-trigger', ContextMenuTriggerController);
app.register('data-table', DataTableController);
app.register('drawer-manager', DrawerManagerController);
app.register('content-loader', ContentLoaderController);
