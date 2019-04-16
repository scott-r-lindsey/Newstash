import React from "react";
import PropTypes from 'prop-types';
import gql from "graphql-tag";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';
import { Query } from "react-apollo";

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";

const styles = theme => ({
  wrap: {
    padding: '5px',
    backgroundColor:'white',
  },
});

class Post extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  loading = false;

  componentDidMount() {
    console.log('did mount');
  }

  render() {

    const id = this.props.match.params.id;
    const { classes } = this.props;

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        <Query
          query={gql`
            {
              post(id: ${id}) {
                id
                active
                pinned
                title
                slug
                year
                image
                image_x
                image_y
                description
                lead
                fold
                published_at
                user {
                  first_name
                  last_name
                }
              }
            }
          `}
        >

          {({ loading, error, data }) => {
            if (loading) return <Loading />;
            if (error) return <p>Error </p>;

            return (
              <div className={classes.wrap}>
                <br/>
                <br/>
                { data.post.title }<br />
                { data.post.description }
              </div>
            );
          }}

        </Query>

      </div>
    );
  }
}

export default withStyles(styles)(Post);
