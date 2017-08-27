import axios from 'axios';

export function fetchCharacter(characterName) {
  return function(dispatch) {
    dispatch({type: "FETCH_CHARACTER"});
    axios.get(getQueryURL(characterName))
      .then((response) => {
        dispatch({type: "FETCH_CHARACTER_FULFILLED", payload: response.data})
      })
      .catch((err) => {
        dispatch({type: "FETCH_CHARACTER_REJECTED", payload: err})
      });
  }
}

function getQueryURL(characterName) {
  return 'http://api.illusions-guild.com/wow/character.php?action=base&name=' + characterName + '&realm=Draenor&region=EU';
}
