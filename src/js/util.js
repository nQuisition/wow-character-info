const baseUrl = 'http://api.illusions-guild.com/wow/'

export function getIconUrl(context, icon, small=false) {
  if(context === 'talent' || context === 'talents')
    context = 'spell';
  let result = baseUrl + 'media/images/wow/' + context + '/' + icon;
  if(context === 'race' || context === 'class')
    result += small?'_s':'_l';
  return  result + '.jpg';
}

export function getWowheadLink(context, id) {
  if(context === 'talent' || context === 'talents')
    context = 'spell';
  return 'http://www.wowhead.com/' + context + '=' + id;
}

export function getRelStringForItem(item) {
  let rel = '';
  if(item.bonusList !== null && item.bonusList !== '') {
    rel += 'bonus=' + item.bonusList + '&';
  }
  if(item.setList !== null && item.setList !== '') {
    rel += 'pcs=' + item.setList + '&';
  }
  if(item.gems !== null && item.gems.length > 0) {
    rel += 'gems=';
    for(let i = 0; i < item.gems.length; i++) {
      rel += item.gems[i] + ':';
    }
    rel += '&';
  }
  if(item.enchant !== null && item.enchant !== '') {
    rel += 'ench=' + item.enchant + '&';
  }
  return rel;
}
