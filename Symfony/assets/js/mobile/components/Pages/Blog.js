import PropTypes from 'prop-types';
import React from "react";
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import postsGql from 'raw-loader!../../raw/graphql/posts.graphql';
import { generatePostLink, generatePostImageLink } from "../../util.js";

const styles = theme => ({
  wrap: {
    padding: '3vw 2vw 10vw 2vw',
    backgroundColor:'white',
    minHeight: 'calc(100vh - 56px)',
    fontFamily: Constants.BoringFont,
  },
  head: {
  },
  firstImage: {
    width:'100%',
    margin: '5px 0vw 0 0vw',
  },
  firstPost: {
    textAlign: 'center',
    fontSize: '4vh',
    lineHeight: '5vh',
  },
  postLink: {
    color: 'black',
    textDecoration: 'none',
    lineHeight: '5vh',
  },
  title: {
    fontSize: '4vh',
    lineHeight: '7vh',
  },
  post: {
    borderTop: '1px solid #ccc',
    textAlign: 'center',
    fontSize: '4vh',
    lineHeight: '5vh',
  },
  lead: {
    '& p': {
      margin: 0,
      fontSize: '3vh',
      lineHeight: '4vh',
    },
    maxHeight:'40vh',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    display: '-webkit-box',
    WebkitLineClamp: '3',
    WebkitBoxOrient: 'vertical',
  },

});

class Blog extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  renderPosts(posts) {
    const { classes } = this.props;

    return (
      <div className={classes.wrap}>

        {posts.edges.map((post, index) => (
          (index === 0) ? // first post
            <div key={post.node.id} >
              <Link to={generatePostLink(post.node)} className={classes.postLink} >
                { (console.log(post.node), post.node.image) ?
                  <img
                    className={classes.firstImage}
                    src={generatePostImageLink(post.node)} /> : null
                }
                <div className={classes.firstPost}>
                  <strong className={classes.title}>{post.node.title}</strong>
                  <div
                    className={classes.lead}
                    dangerouslySetInnerHTML={{__html: post.node.lead.trim()}} />
                </div>
              </Link>
            </div>
           : // all other posts
            <div key={post.node.id} className={classes.post} >
              <Link to={generatePostLink(post.node)} className={classes.postLink} >
                <strong>{post.node.title}</strong>
              </Link>
            </div>
        ))}

      </div>
    );
  }

  render() {

    const id = this.props.match.params.id;
    const { initialProps } = this.props;

    let posts = false;
    if (  (initialProps.data) &&
          (initialProps.data.posts)) {

      posts = initialProps.data.posts;
    }

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        { ( posts ) ?
          <div>
            { this.renderPosts(posts) }
          </div> :
          <Query query={gql(postsGql)} >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderPosts(data.posts);
            }}
          </Query>
        }

      </div>
    );
  }
}

export default withStyles(styles)(Blog);
