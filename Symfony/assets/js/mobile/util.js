
import React from "react";
import Icon from '@material-ui/core/Icon';

export const fiveStars = function (count) {

  count = Math.trunc(count); // no strings plz

  switch (count) {
    case 5:
      return (<div><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon></div>);
    case 4:
      return (<div><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star_border</Icon></div>);
    case 3:
      return (<div><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon></div>);
    case 2:
      return (<div><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon></div>);
    case 1:
      return (<div><Icon fontSize="inherit">star</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon></div>);
    default:
      return (<div><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon><Icon fontSize="inherit">star_border</Icon></div>);
  }
}

export const generateWorkLink = function (work) {
  return '/book/' + (work.id ? work.id : work.work_id) + '/' + work.slug;
}

export const generatePostLink = function (post) {
  return '/blog/' + post.id + '/' + post.slug;
}

