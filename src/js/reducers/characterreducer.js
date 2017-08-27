export default function reducer(state = {
  character: null,
  fetching: false,
  fetched: false,
  error: null
}, action) {
  switch(action.type) {
    case "FETCH_CHARACTER": {
      return {...state, fetching: true};
    }
    case "FETCH_CHARACTER_FULFILLED": {
      let result = {
        ...state,
        character: action.payload,
        fetching: false,
        fetched: true
      };
      return result;
    }
    case "FETCH_CHARACTER_REJECTED": {
      return {...state, fetching: false, error: action.payload};
    }
  }
  return state;
}
