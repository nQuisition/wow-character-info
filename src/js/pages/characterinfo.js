import React, { PropTypes } from 'react'
import { connect } from 'react-redux';

import * as characterActions from '../actions/characteractions';
import * as Util from '../util';

import moment from 'moment'
import 'moment-timezone'

import icon_hc from '../../../media/images/wow/skull.png'

class CharacterInfo extends React.Component {
  componentWillMount() {
    this.fetchCharacter(this.props.params.name);
  }

  componentWillReceiveProps(nextProps) {
    if(this.props.params.name !== nextProps.params.name) {
      this.fetchCharacter(nextProps.params.name);
    }
  }

  fetchCharacter(name) {
    this.props.dispatch(characterActions.fetchCharacter(name));
  }

  render () {
    if(this.props.character === null) {
      return(<div></div>);
    }
    const character = this.props.character;
    const charItems = character.items.map((e,k) => <ItemFrame item={e} key={k}/>);

    return(
      <div>
        <div class="article-title">
          <img src={character.thumbnail}/>
          <br/>
          <h1>{character.name}</h1>
          {character.realm}-{character.region}
          <br/>
        </div>
        <div>
          <img src={Util.getIconUrl('class', character.classIcon)}/>
          {character.gender==1?'Female':'Male'} {character.raceName} {character.className}
          <br/>
          <img src={Util.getIconUrl('spec', character.specIcon)}/>
          {character.specName}
          <br/>
        </div>
        <div>
          {charItems}
        </div>
      </div>
    );
  }
}

class ItemFrame extends React.Component {
  render() {
    const item = this.props.item;
    const itemStyle = {
      color: item.qualityColor
    };
    const imageStyle = {
      width: '32px',
      height: '32px'
    }
    const relString = Util.getRelStringForItem(item);

    return (
      <div>
        <a href={Util.getWowheadLink('item', item.id)} target='_blank' rel={relString}>
          <img src={Util.getIconUrl('item', item.icon)} style={imageStyle}/>
        </a>
        <a href={Util.getWowheadLink('item', item.id)} target='_blank' rel={relString} style={itemStyle}>
          {item.name} ({item.ilvl})
        </a>
      </div>
    );
  }
}

export default connect((store) => {
  return {
    character: store.character.character,
    characterFetching: store.character.fetching,
    characterFetched: store.character.fetched,
    error: store.character.error
  };
})(CharacterInfo);
