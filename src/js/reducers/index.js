import { combineReducers } from 'redux';

import player from './playerreducer';
import character from './characterreducer';

export default combineReducers ({
  player,
  character
});
