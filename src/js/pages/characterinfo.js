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
    const charSpecs = character.specs.map((e,k) => <SpecFrame spec={e} selected={e.name===character.specName} key={k}/>);

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
          {charSpecs}
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

class SpecFrame  extends React.Component {
  render() {
    const style = {
      margin: '10px',
      padding: '15px',
      width:'50%',
      border: '1px solid ' + (this.props.selected?'green':'gray'),
      display: 'flex',
      justifyContent: 'space-between'
    }
    const talents = this.props.spec.talents.map((e,k) => <TalentFrame talent={e} key={k}/>);

    return(
      <div>
        <div style={style}>
          {talents}
        </div>
      </div>
    )
  }
}

class TalentFrame extends React.Component {
  render() {
    const talent = this.props.talent;
    const styleImg = {
      width: '32px',
      height: '32px'
    }

    return (
      <a href={Util.getWowheadLink('talent', talent.spellid)} target='_blank'>
        <img src={Util.getIconUrl('talent', talent.icon)} style={styleImg}/>
      </a>
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
