export default function reducer(state = {
  main: [],
  others: [],
  fetching: false,
  fetched: false,
  error: null
}, action) {
  switch(action.type) {
    case "FETCH_PLAYER": {
      return {...state, fetching: true};
    }
    case "FETCH_PLAYER_FULFILLED": {
      let result = {
        ...state,
        main: action.payload,
        fetching: false,
        fetched: true
      };
      if (typeof state.others === 'undefined' || state.others.length <= 0) {
        result = {...result, others: createOthersArray(result.main)};
      }
      return result;
    }
    case "FETCH_PLAYER_REJECTED": {
      return {...state, fetching: false, error: action.payload};
    }
  }
  return state;
}

function createOthersArray(main) {
  return main.map((e) => {
    const newElement = {
      name: e.name,
      difficulty: e.difficulty,
      players: []
    };
    return newElement;
  });
}
