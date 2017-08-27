import axios from 'axios';
import config from '../config/config';

export function fetchPlayer(playerName) {
  return function(dispatch) {
    dispatch({type: "FETCH_PLAYER"});
    axios.get(getQueryURL(playerName))
      .then((response) => {
        dispatch({type: "FETCH_PLAYER_FULFILLED", payload: response.data})
      })
      .catch((err) => {
        dispatch({type: "FETCH_PLAYER_REJECTED", payload: err})
      });
  }
}

export function fetchZones() {
  return function(dispatch) {
    dispatch({type: "FETCH_ZONES"});
    axios.get('https://www.warcraftlogs.com:443/v1/zones?api_key=' + config.wlApiKey)
      .then((response) => {
        console.log("Zones",response.data);
        dispatch({type: "FETCH_ZONES_FULFILLED", payload: response.data})
      })
      .catch((err) => {
        dispatch({type: "FETCH_ZONES_REJECTED", payload: err})
      });
  }
}

function getQueryURL(name) {
  return 'https://www.warcraftlogs.com:443/v1/parses/character/' + name + '/Draenor/EU?metric=dps&api_key=' + config.wlApiKey;
  //return 'https://www.warcraftlogs.com:443/v1/parses/character/' + name + '/Draenor/EU?zone=11&partition=2&metric=dps&api_key=' + config.wlApiKey;
}
