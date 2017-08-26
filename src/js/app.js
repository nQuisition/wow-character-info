import React from "react";
import ReactDOM from "react-dom";
import { Provider } from 'react-redux';

import MainRouter from './components/mainrouter';
import store from './store';

const app = document.getElementById('app');

ReactDOM.render(
  <Provider store={store}>
    <MainRouter />
  </Provider>, app);
